<?php
require_once "models/usuario/UsuarioM.php";
require_once __DIR__ . '/../../lib/Seguridad.php';
require_once __DIR__ . '/../../lib/Archivos.php';

class UsuarioC {
    private $modelo;

    public function __construct() {
        $this->modelo = new UsuarioM();
    }

    public function index() {
        if (isset($_SESSION['id_usuario'])) {
            header('Location: index.php?page=' . $_SESSION['rol_slug']);
            exit;
        }
        require "views/layouts/header.php";
        require "views/usuario/auth/LoginV.php";
        require "views/layouts/footer.php";
    }

    public function logIn() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=logIn');
            exit;
        }

        Seguridad::exigirPost();
        $alias = Seguridad::texto($_POST, 'txtalias');
        $password = Seguridad::texto($_POST, 'txtpassword');
        $_SESSION['alias_login'] = $alias;
        if ($alias === '' || $password === '') Avisos::ir('logIn', 'error', 'Escribe tu alias y contraseña.');
        try {
            $usuario = $this->modelo->logIn($alias, md5($password));
        } catch (Throwable $e) {
            Avisos::ir('logIn', 'error', Avisos::registrarError($e, 'iniciar sesión'));
        }

        if ($usuario) {
            session_regenerate_id(true);
            unset($_SESSION['alias_login']);
            $_SESSION['alias'] = $usuario->alias;
            $_SESSION['rol_id'] = $usuario->rol_id;
            $_SESSION['rol_slug'] = $usuario->slug;
            $_SESSION['id_usuario'] = $usuario->id;
            header('Location: ' . urlsite . '?page=' . $_SESSION['rol_slug']);
            exit;
        }

        Avisos::ir('logIn', 'error', 'Alias o contraseña incorrectos. Inténtalo nuevamente.');
    }

    public function mostrarPerfil() {
        if (!isset($_SESSION['id_usuario'])) {
            header('Location: ' . urlsite . '?page=logIn');
            exit;
        }

        $idUsuario = (int) $_SESSION['id_usuario'];
        $rolUsuario = $_SESSION['rol_slug'] ?? '';
        $usuario = $this->modelo->obtenerPorID($idUsuario);

        require "views/layouts/header.php";
        require "views/layouts/navbar.php";

        if ($rolUsuario === 'supervisor' || $rolUsuario === 'admin') {
            require_once "models/siniestro/SiniestroM.php";
            $siniestroModel = new SiniestroM();
            $datosDashboard = $siniestroModel->getDashboardAdmin();
            require "views/usuario/AjustadorAdminAuthV.php";
        } elseif ($rolUsuario === 'ajustador') {
            require "views/usuario/AjustadorAdminAuthV.php";
        } elseif ($rolUsuario === 'asegurado') {
            require "views/usuario/UserAuthV.php";
        } else {
            header('Location: index.php?page=logIn');
            exit;
        }

        require "views/layouts/footer.php";
    }

    public static function logOut() {
        session_unset();
        session_destroy();
        header('Location: ' . urlsite);
        exit;
    }

    public function mostrarRegistro() {
        if (isset($_SESSION['id_usuario'])) {
            header('Location: index.php?page=' . $_SESSION['rol_slug']);
            exit;
        }
        require "views/layouts/header.php";
        require "views/usuario/auth/RegisterV.php";
        require "views/layouts/footer.php";
    }

    public function procesarRegistro() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=sigIn');
            exit;
        }

        Seguridad::exigirPost();
        $password = $_POST['txtPassword'] ?? null;
        if (!is_string($password) || $password === '') Avisos::ir('sigIn', 'error', 'Escribe una contraseña.');
        if ($password !== ($_POST['txtPassword_Confirm'] ?? null)) Avisos::ir('sigIn', 'error', 'Las contraseñas no coinciden.');
        try {
            $email = Seguridad::texto($_POST, 'txtEmail');
            $alias = Seguridad::texto($_POST, 'txtAlias');
            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) throw new RuntimeException('Escribe un correo electrónico válido de hasta 100 caracteres.');
            if ($alias === '' || strlen($alias) > 50) throw new RuntimeException('Escribe un alias de hasta 50 caracteres.');
            $resultado = $this->modelo->sigIn(
                Seguridad::texto($_POST, 'txtNombres'),
                Seguridad::texto($_POST, 'txtApellidoP'),
                Seguridad::texto($_POST, 'txtApellidoM'),
                Seguridad::texto($_POST, 'dateNacimiento'),
                Seguridad::texto($_POST, 'genero'),
                $email, md5($password), $alias, $this->leerFoto()
            );
            if (!$resultado) throw new RuntimeException('No se pudo crear la cuenta. Revisa los datos e inténtalo nuevamente.');
            Avisos::ir('logIn', 'success', 'Cuenta creada correctamente. Ya puedes iniciar sesión con tu alias.');
        } catch (Throwable $e) {
            Avisos::ir('sigIn', 'error', Avisos::registrarError($e, 'registrar la cuenta'));
        }
    }

    public function EditarPerfil() {
        if (!isset($_SESSION['id_usuario'])) {
            header('Location: ' . urlsite);
            exit;
        }

        $usuario = $this->modelo->obtenerPorID((int) $_SESSION['id_usuario']);
        require "views/layouts/header.php";
        require "views/layouts/navbar.php";
        require "views/usuario/EditProfileV.php";
        require "views/layouts/footer.php";
    }

    public function procesarEdicion() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['btnEdit'])) {
            header('Location: index.php?page=miPerfil');
            exit;
        }
        if (!isset($_SESSION['id_usuario'])) {
            header('Location: index.php?page=logIn');
            exit;
        }

        Seguridad::exigirPost();
        try {
            $idUsuario = (int) $_SESSION['id_usuario'];
            $alias = Seguridad::texto($_POST, 'txtAlias');
            $emailFinal = Seguridad::texto($_POST, 'txtNuevoEmail') ?: Seguridad::texto($_POST, 'txtEmailActual');
            if ($alias === '' || strlen($alias) > 50 || !filter_var($emailFinal, FILTER_VALIDATE_EMAIL) || strlen($emailFinal) > 100) {
                throw new RuntimeException('Revisa el alias (máximo 50 caracteres) y el correo electrónico (máximo 100).');
            }
            $nueva = $_POST['txtPasswordNueva'] ?? '';
            $passwordNueva = is_string($nueva) && $nueva !== '' ? md5($nueva) : null;
            $resultado = $this->modelo->editarUsuario($idUsuario, $emailFinal, $passwordNueva, $alias, $this->leerFoto());
            if (!$resultado) throw new RuntimeException('No se pudieron guardar los cambios del perfil.');
            $_SESSION['alias'] = $alias;
            Avisos::ir('miPerfil', 'success', 'Tu perfil se actualizó correctamente.');
        } catch (Throwable $e) {
            Avisos::ir('miPerfil', 'error', Avisos::registrarError($e, 'actualizar el perfil'));
        }
    }

    private function leerFoto() {
        if (!isset($_FILES['foto_perfil']) || ($_FILES['foto_perfil']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
        $foto = $_FILES['foto_perfil'];
        $archivos = Archivos::validarCarga([
            'error' => [$foto['error'] ?? -1], 'tmp_name' => [$foto['tmp_name'] ?? '']
        ]);
        if (!$archivos || $archivos[0]['tipo'] !== 'Foto') throw new RuntimeException('La foto de perfil debe ser JPG, PNG, WEBP o GIF.');
        $contenido = file_get_contents($archivos[0]['tmp']);
        if ($contenido === false) throw new RuntimeException('No se pudo leer la foto de perfil.');
        return $contenido;
    }
}
?>
