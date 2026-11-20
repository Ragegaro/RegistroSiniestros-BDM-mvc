<?php
require_once "models/usuario/UsuarioM.php";

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

        $alias = trim($_POST['txtalias'] ?? '');
        $pass = md5(trim($_POST['txtpassword'] ?? ''));
        $usuario = $this->modelo->logIn($alias, $pass);

        if ($usuario) {
            $_SESSION['alias'] = $usuario->alias;
            $_SESSION['rol_id'] = $usuario->rol_id;
            $_SESSION['rol_slug'] = $usuario->slug;
            $_SESSION['id_usuario'] = $usuario->id;
            header('Location: ' . urlsite . '?page=' . $_SESSION['rol_slug']);
            exit;
        }

        header('Location: ' . urlsite . '?page=logIn&msg=No coinciden las credenciales');
        exit;
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

        if (($_POST['txtPassword'] ?? '') !== ($_POST['txtPassword_Confirm'] ?? '')) {
            header('Location: index.php?page=sigIn&error=password');
            exit;
        }

        $passwordCifrada = md5($_POST['txtPassword'] ?? '');
        $fotoBinaria = null;
        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
            $fotoBinaria = file_get_contents($_FILES['foto_perfil']['tmp_name']);
        }

        $resultado = $this->modelo->sigIn(
            trim($_POST['txtNombres'] ?? ''),
            trim($_POST['txtApellidoP'] ?? ''),
            trim($_POST['txtApellidoM'] ?? ''),
            $_POST['dateNacimiento'] ?? '',
            $_POST['genero'] ?? null,
            trim($_POST['txtEmail'] ?? ''),
            $passwordCifrada,
            trim($_POST['txtAlias'] ?? ''),
            $fotoBinaria
        );

        header('Location: index.php?page=' . ($resultado ? 'logIn&success=1' : 'sigIn&error=1'));
        exit;
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

        $idUsuario = (int) $_SESSION['id_usuario'];
        $alias = trim($_POST['txtAlias'] ?? '');
        $emailFinal = !empty($_POST['txtNuevoEmail']) ? trim($_POST['txtNuevoEmail']) : trim($_POST['txtEmailActual'] ?? '');
        $passwordNueva = !empty($_POST['txtPasswordNueva']) ? md5($_POST['txtPasswordNueva']) : null;
        $fotoBinaria = null;
        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
            $fotoBinaria = file_get_contents($_FILES['foto_perfil']['tmp_name']);
        }

        $resultado = $this->modelo->editarUsuario($idUsuario, $emailFinal, $passwordNueva, $alias, $fotoBinaria);
        if ($resultado) $_SESSION['alias'] = $alias;
        header('Location: index.php?page=miPerfil&' . ($resultado ? 'success=1' : 'error=1'));
        exit;
    }
}
?>
