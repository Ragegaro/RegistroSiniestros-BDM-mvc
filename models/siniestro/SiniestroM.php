<?php
require_once __DIR__ . '/../Conexion.php';
require_once __DIR__ . '/../Procedimientos.php';
require_once __DIR__ . '/MultimediaM.php';

class SiniestroM {
    private $_db;
    public function __construct() { $this->_db = new Conexion(); }

    private function consultar(string $sp, array $parametros = [], int $modo = PDO::FETCH_ASSOC): array {
        $pdo = $this->_db->conectar();
        try { return Procedimientos::filas($pdo, $sp, $parametros, $modo); }
        finally { $this->_db->desconectar(); }
    }
    private function una(string $sp, array $parametros = []) {
        $pdo = $this->_db->conectar();
        try { return Procedimientos::fila($pdo, $sp, $parametros); }
        finally { $this->_db->desconectar(); }
    }

    /** SQL y archivos fisicos forman una sola operacion de aplicacion; rollback y limpieza ante errores. */
    public function reportarConEvidencias($datos, $idUsuario, $archivos) {
        $pdo = $this->_db->conectar();
        $rutas = [];
        try {
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $pdo->beginTransaction();
            $id = (int) Procedimientos::valor($pdo, 'sp_registrarSiniestro', [
                $datos['fecha'], $datos['hora'], $datos['ubicacion'], $datos['descripcion'],
                $datos['SelectPoliza'], (int) $idUsuario
            ]);
            if ($id<=0) throw new RuntimeException('No se pudo crear el expediente.');
            foreach ($archivos as $archivo) {
                $rutas[] = MultimediaM::insertarRuta($pdo, $archivo, $id);
            }
            // Comprobar archivos antes de confirmar la transaccion SQL.
            foreach ($rutas as $ruta) {
                clearstatcache(true, $ruta);
                if (!is_file($ruta) || !is_readable($ruta)) {
                    throw new RuntimeException('Una evidencia desaparecio antes de confirmar el siniestro.');
                }
            }
            $pdo->commit();
            return $id;
        } catch (Throwable $e) {
            Conexion::revertir($pdo);
            foreach ($rutas as $ruta) if (is_file($ruta)) @unlink($ruta);
            throw $e;
        } finally { $this->_db->desconectar(); }
    }

    public function buscarSiniestros(int $idUsuario, array $filtros = []): array {
        return $this->consultar('sp_buscar_siniestros', [
            $idUsuario,
            (string)($filtros['q'] ?? ''),
            (int)($filtros['estatus'] ?? 0),
            (int)($filtros['ajustador'] ?? 0),
            $filtros['desde'] ?? null,
            $filtros['hasta'] ?? null,
        ], PDO::FETCH_OBJ);
    }
    public function obtenerSiniestrosPorRol($idUsuario, $rolSlug) {
        // El rol se decide en SQL por la cuenta activa, no por $rolSlug de sesion.
        return $this->buscarSiniestros((int)$idUsuario);
    }
    public function obtenerPorID($idSiniestro, $idUsuario) {
        return $this->una('sp_detalle_siniestro', [(int)$idSiniestro, (int)$idUsuario]);
    }
    public function obtenerMultimediaPorSiniestro($idSiniestro, $idUsuario) {
        return $this->consultar('sp_listar_multimedia', [(int)$idSiniestro, (int)$idUsuario], PDO::FETCH_OBJ);
    }
    public function catalogoAjustadoresBusqueda($idSupervisor) {
        return $this->consultar('sp_catalogo_ajustadores_busqueda', [(int)$idSupervisor]);
    }
    public function obtenerAjustadoresActivos($idSupervisor) {
        return $this->consultar('sp_ajustadores_activos', [(int)$idSupervisor]);
    }
    public function asignarAjustador($idSiniestro, $idAjustador, $idSupervisor) {
        $pdo=$this->_db->conectar();
        try {
            $pdo->beginTransaction();
            Procedimientos::ejecutar($pdo,'sp_reasignar_ajustador',[(int)$idSiniestro,(int)$idAjustador,(int)$idSupervisor]);
            $pdo->commit();
            return true;
        } catch(Throwable $e) { Conexion::revertir($pdo); throw $e; }
        finally { $this->_db->desconectar(); }
    }
    public function getHistorialEstatus($idSiniestro, $idUsuario) {
        return $this->consultar('sp_historial_siniestro', [(int)$idSiniestro,(int)$idUsuario],PDO::FETCH_OBJ);
    }
    public function obtenerEstatus() { return $this->consultar('sp_catalogo_estatus',[],PDO::FETCH_OBJ); }
    public function cambiarEstatus($idSiniestro,$idEstatus,$idUsuario,$rol) {
        $pdo=$this->_db->conectar();
        try {
            $pdo->beginTransaction();
            Procedimientos::ejecutar($pdo,'sp_cambiar_estatus',[(int)$idSiniestro,(int)$idEstatus,(int)$idUsuario]);
            $pdo->commit();
            return true;
        } catch(Throwable $e) { Conexion::revertir($pdo); throw $e; }
        finally { $this->_db->desconectar(); }
    }
    public function guardarEvaluacion($idSiniestro,$idAjustador,$diagnostico) {
        $pdo=$this->_db->conectar();
        try { return Procedimientos::ejecutar($pdo,'sp_guardar_evaluacion',[(int)$idSiniestro,(int)$idAjustador,$diagnostico]); }
        finally { $this->_db->desconectar(); }
    }
    public function obtenerEvaluacion($idSiniestro,$idUsuario) {
        return $this->una('sp_obtener_evaluacion',[(int)$idSiniestro,(int)$idUsuario]) ?: null;
    }
    public function getDashboardAdmin() {
        return [
            'resumen_estatus'=>$this->consultar('sp_dashboard_estados'),
            'ajustadores'=>$this->consultar('sp_dashboard_ajustadores'),
            'clientes'=>$this->consultar('sp_dashboard_clientes'),
        ];
    }
}
