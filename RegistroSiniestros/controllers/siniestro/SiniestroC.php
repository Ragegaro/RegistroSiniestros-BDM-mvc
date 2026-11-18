<?php
require_once "models/siniestro/SiniestroM.php";
require_once __DIR__ . '/../../lib/Seguridad.php';
require_once __DIR__ . '/../../lib/Archivos.php';

class SiniestroC {
    private $modelo;

    public function __construct() {
        $this->modelo = new SiniestroM();
    }

    private function exigirSesion() {
        if (!isset($_SESSION['id_usuario'])) {
            header("Location: index.php");
            exit;
        }
    }

    private function puedeVerSiniestro($siniestro) {
        return Seguridad::puedeVer($siniestro);
    }

    public function mostrarFormulario() {
        $this->exigirSesion();
        if (($_SESSION['rol_slug'] ?? '') !== 'asegurado') {
            header("Location: index.php?page=misSiniestros");
            exit;
        }

        require_once "models/poliza/PolizaM.php";
        $modeloPoliza = new PolizaM();
        $misPolizas = $modeloPoliza->verPolizas($_SESSION['id_usuario']);
        $misPolizas = is_array($misPolizas) ? $misPolizas : [];
        $anterior = $_SESSION['reporte_anterior'] ?? [];
        unset($_SESSION['error_reporte'], $_SESSION['reporte_anterior']);

        require "views/layouts/header.php";
        require "views/layouts/navbar.php";
        require "views/siniestros/reporteV.php";
        require "views/layouts/footer.php";
    }

    public function agregarSiniestro() {
        $this->exigirSesion();
        if (($_SESSION['rol_slug'] ?? '') !== 'asegurado' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?page=misSiniestros");
            exit;
        }

        Seguridad::exigirPost();
        $datos = [
            'fecha' => Seguridad::texto($_POST, 'fecha'),
            'hora' => Seguridad::texto($_POST, 'hora'),
            'ubicacion' => Seguridad::texto($_POST, 'ubicacion'),
            'descripcion' => Seguridad::texto($_POST, 'descripcion'),
            'SelectPoliza' => Seguridad::entero($_POST, 'SelectPoliza')
        ];
        try {
            $fecha = DateTime::createFromFormat('!Y-m-d', $datos['fecha']);
            if (!$fecha || $fecha->format('Y-m-d') !== $datos['fecha'] || !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/D', $datos['hora'])) {
                throw new RuntimeException('Escribe una fecha y una hora válidas.');
            }
            if ($datos['SelectPoliza'] <= 0 || $datos['ubicacion'] === '' || $datos['descripcion'] === '') {
                throw new RuntimeException('Completa la póliza, ubicación y descripción.');
            }
            if (strlen($datos['ubicacion']) > 10000 || strlen($datos['descripcion']) > 50000) throw new RuntimeException('La ubicación o descripción es demasiado larga.');
            $archivos = Archivos::validarCarga($_FILES['evidencias'] ?? null);
            $id = $this->modelo->reportarConEvidencias($datos, (int) $_SESSION['id_usuario'], $archivos);
            Avisos::ir('detalle', 'success', 'Siniestro y evidencias guardados correctamente.', ['id' => $id]);
        } catch (Throwable $e) {
            $_SESSION['reporte_anterior'] = $datos;
            Avisos::ir('report', 'error', Avisos::registrarError($e, 'guardar el reporte') . ' Vuelve a seleccionar los archivos antes de enviar.');
        }
        exit;
    }

    public function listarSiniestros() {
        $this->exigirSesion();
        $idUsuario = (int) $_SESSION['id_usuario'];
        $rolUsuario = $_SESSION['rol_slug'] ?? '';
        $misSiniestros = $this->modelo->obtenerSiniestrosPorRol($idUsuario, $rolUsuario);
        $misSiniestros = is_array($misSiniestros) ? $misSiniestros : [];

        require "views/layouts/header.php";
        require "views/layouts/navbar.php";
        require "views/siniestros/listarSiniestrosV.php";
        require "views/layouts/footer.php";
    }

    public function verDetalledSiniestro() {
        $this->exigirSesion();
        header('Cache-Control: private, no-store, max-age=0');
        $idSiniestro = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $siniestro = $idSiniestro > 0 ? $this->modelo->obtenerPorID($idSiniestro) : false;

        if (!$siniestro) {
            Avisos::paginaError('Siniestro no encontrado.', 404);
        }

        if (!$this->puedeVerSiniestro($siniestro)) {
            Avisos::ir('misSiniestros', 'error', 'No tienes acceso a ese siniestro.');
        }

        $siniestro['multimedia'] = $this->modelo->obtenerMultimediaPorSiniestro($idSiniestro);
        $historial = $this->modelo->getHistorialEstatus($idSiniestro);
        $estatusDisponibles = $this->modelo->obtenerEstatus();
        $evaluacion = $this->modelo->obtenerEvaluacion($idSiniestro);
        $ajustadores = Seguridad::esSupervisor() ? $this->modelo->obtenerAjustadoresActivos() : [];

        require_once __DIR__ . '/../../models/siniestro/ChatM.php';
        $chatDatos = ['mensajes' => [], 'participantes' => [], 'hay_anteriores' => false];
        $chatError = '';
        try {
            $antes = max(0, Seguridad::entero($_GET, 'chat_antes'));
            $chatDatos = (new ChatM())->leer($idSiniestro, (int) $_SESSION['id_usuario'], $antes);
        } catch (ChatError $error) {
            $chatError = $error->getMessage();
        } catch (Throwable $error) {
            Avisos::registrarError($error, 'cargar el chat del expediente');
            $chatError = 'No se pudo cargar el chat. Recarga el expediente para intentarlo nuevamente.';
        }
        $borrador = $_SESSION['chat_borrador'] ?? [];
        $chatBorrador = (int) ($borrador['id'] ?? 0) === $idSiniestro ? ($borrador['mensaje'] ?? '') : '';
        unset($_SESSION['chat_borrador']);

        require "views/layouts/header.php";
        require "views/layouts/navbar.php";
        require "views/siniestros/detalle.php";
        require "views/layouts/footer.php";
    }

    public function guardarEvaluacion() {
        $this->exigirSesion();
        $rol = $_SESSION['rol_slug'] ?? '';
        if (!in_array($rol, ['ajustador', 'supervisor', 'admin'], true) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?page=misSiniestros");
            exit;
        }
        Seguridad::exigirPost();

        $idSiniestro = isset($_POST['id_siniestro']) ? (int) $_POST['id_siniestro'] : 0;
        $diagnostico = Seguridad::texto($_POST, 'diagnostico');
        $siniestro = $idSiniestro > 0 ? $this->modelo->obtenerPorID($idSiniestro) : false;

        if (!$siniestro || !$this->puedeVerSiniestro($siniestro) || $diagnostico === '') {
            Avisos::ir('misSiniestros', 'error', 'No se pudo guardar la evaluación. Revisa el diagnóstico y tus permisos sobre el expediente.');
        }

        try {
            $resultado = $this->modelo->guardarEvaluacion($idSiniestro, (int) $_SESSION['id_usuario'], $diagnostico);
            Avisos::ir('detalle', $resultado ? 'success' : 'error', $resultado ? 'Evaluación guardada correctamente.' : 'No se pudo guardar la evaluación.', ['id' => $idSiniestro]);
        } catch (Throwable $e) {
            Avisos::ir('detalle', 'error', Avisos::registrarError($e, 'guardar la evaluación'), ['id' => $idSiniestro]);
        }
    }

    public function actualizarEstatus() {
        $this->exigirSesion();
        $rol = $_SESSION['rol_slug'] ?? '';
        if (!in_array($rol, ['supervisor', 'admin'], true) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?page=misSiniestros");
            exit;
        }
        Seguridad::exigirPost();

        $idSiniestro = isset($_POST['id_siniestro']) ? (int) $_POST['id_siniestro'] : 0;
        $idEstatus = isset($_POST['estatus_id']) ? (int) $_POST['estatus_id'] : 0;

        if ($idSiniestro <= 0 || $idEstatus <= 0) {
            Avisos::ir('misSiniestros', 'error', 'Selecciona un siniestro y un estatus válidos.');
        }

        try {
            $resultado = $this->modelo->cambiarEstatus($idSiniestro, $idEstatus);
            Avisos::ir('detalle', $resultado ? 'success' : 'error', $resultado ? 'Estatus actualizado correctamente.' : 'No se pudo actualizar el estatus.', ['id' => $idSiniestro]);
        } catch (Throwable $e) {
            Avisos::ir('detalle', 'error', Avisos::registrarError($e, 'actualizar el estatus'), ['id' => $idSiniestro]);
        }
    }

    public function mostrarAsignaciones() {
        Seguridad::exigirSupervisor();
        $filtro = ($_GET['filtro'] ?? '') === 'pendientes' ? 'pendientes' : 'todos';
        $casos = $this->modelo->obtenerSiniestrosPorRol((int) $_SESSION['id_usuario'], $_SESSION['rol_slug']);
        if ($filtro === 'pendientes') {
            $casos = array_filter($casos, function ($caso) {
                return empty($caso->ajustador_id) && strcasecmp(trim($caso->Estatus), 'Cerrado') !== 0;
            });
        }
        $ajustadores = $this->modelo->obtenerAjustadoresActivos();
        require 'views/layouts/header.php';
        require 'views/layouts/navbar.php';
        require 'views/siniestros/asignaciones.php';
        require 'views/layouts/footer.php';
    }

    public function asignarAjustador() {
        Seguridad::exigirSupervisor();
        Seguridad::exigirPost();
        $id = Seguridad::entero($_POST, 'id_siniestro');
        $ajustador = Seguridad::entero($_POST, 'ajustador_id');
        $destino = ($_POST['volver'] ?? '') === 'detalle' ? 'detalle' : 'asignaciones';
        try {
            if ($id <= 0 || $ajustador < 0 || !isset($_POST['ajustador_id']) || filter_var($_POST['ajustador_id'], FILTER_VALIDATE_INT) === false) {
                throw new RuntimeException('Selecciona un siniestro y un responsable válidos.');
            }
            $this->modelo->asignarAjustador($id, $ajustador);
            Avisos::poner('success', $ajustador > 0 ? 'Ajustador asignado correctamente.' : 'El siniestro quedó sin ajustador.');
        } catch (Throwable $e) {
            Avisos::poner('error', Avisos::registrarError($e, 'guardar la asignación'));
        }
        Avisos::ir($destino, null, '', $destino === 'detalle' ? ['id' => $id] : []);
    }
}
?>
