<?php 
require_once "models/UsuarioM.php";

class UsuarioC {
    private $modelo;
    
    public function __construct() {
        $this->modelo = new UsuarioM();
    }

    public function mostrarRegistro() {
        require "views/auth/RegisterV.php";
    }

    public function procesarRegistro() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $passwordCifrada = md5($_POST['txtPassword']);
            $fotoBinaria = null;

            if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == UPLOAD_ERR_OK) {
                $fotoBinaria = file_get_contents($_FILES['foto_perfil']['tmp_name']);
            }

            $resultado = $this->modelo->registrar(
                $_POST['txtNombres'],
                $_POST['txtApellidoP'],
                $_POST['txtApellidoM'],
                $_POST['dateNacimiento'],
                $_POST['genero'],
                $_POST['txtEmail'],
                $passwordCifrada,
                $_POST['txtAlias'],
                $fotoBinaria
            );

            if ($resultado) {
                header("Location: index.php?page=login&success=1");
                exit;
            } else {
                header("Location: index.php?page=sigIn&error=1");
                exit;
            }
        }
    } 

    
    public static function mostrarPerfil() {
        $modelo = new UsuarioM();
        $idUsuario = $_SESSION['id_usuario'];
        $usuario = $modelo->obtenerPorID($idUsuario);
        return $usuario;
    }
    



    public function procesarEdicion() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btnEdit'])) {
        
        // Si por alguna razón la sesión no está iniciada en este punto, la arrancamos
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $idUsuario = isset($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : null; 
        $alias = $_POST['txtAlias'];
        $emailFinal = !empty($_POST['txtNuevoEmail']) ? $_POST['txtNuevoEmail'] : $_POST['txtEmailActual'];

        $passwordNueva = null;
        if (!empty($_POST['txtPasswordNueva'])) {
            $passwordNueva = md5($_POST['txtPasswordNueva']);
        }

        $fotoBinaria = null;
        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == UPLOAD_ERR_OK) {
            $fotoBinaria = file_get_contents($_FILES['foto_perfil']['tmp_name']);
        }

        // LLAMADA AL MODELO: Mandamos exactamente en el orden: ID, Email, Password, Alias, Foto
        $resultado = $this->modelo->editarUsuario($idUsuario, $emailFinal, $passwordNueva, $alias, $fotoBinaria);

        // Este bloque se activará una vez que quitemos los "die" de depuración
        if ($resultado) {
            header("Location: index.php?page=ajustes&success=1");
        } else {
            header("Location: index.php?page=ajustes&error=1");
        }
        exit;
    }
}
}
?>