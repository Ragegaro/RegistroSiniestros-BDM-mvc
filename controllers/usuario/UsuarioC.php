<?php 
    
    require_once "models/usuario/UsuarioM.php";

    class UsuarioC {
        private $modelo;
        
        public function __construct() {
            $this->modelo = new UsuarioM();
        }

       public function index() {
        if (isset($_SESSION['id_usuario'])) { 
            header('location: index.php?page=' . $_SESSION['rol_slug']); 
            exit;                  
        }
        
        require "views/layouts/header.php";
        require "views/usuario/auth/LoginV.php";
        require "views/layouts/footer.php";
        exit;                    
    }
            
        public function logIn(){
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $_alias = trim($_POST ['txtalias']);
                $_pass = md5(trim($_POST['txtpassword']));
                $_usuario = $this->modelo->logIn($_alias,$_pass);

                if ($_usuario){
                    $_SESSION['alias'] = $_usuario->alias;
                    $_SESSION['rol_id']= $_usuario->rol_id;
                    $_SESSION['rol_slug'] = $_usuario->slug;
                    $_SESSION['id_usuario']=$_usuario->id;
                    header('location:' . urlsite . "?page=" . $_SESSION['rol_slug']);
                    exit;
                } else {
                    header('location:'.urlsite."?page=login&msg=No coinciden las credenciales");
                    exit;
                }
            }

        }
        
        public function mostrarPerfil() {
            if (!isset($_SESSION['id_usuario'])) {
               header('location:' . urlsite . "?page=login");
                exit;
            }
            
            $usuario = $this->modelo->obtenerPorID($_SESSION['id_usuario']);
            require "views/layouts/header.php";
            require "views/layouts/navbar.php";
            
            switch ($_SESSION['rol_slug']){
                case 'supervisor':
                    require "views/usuario/AjustadorAdminAuthV.php";

                break;

                case 'ajustador':
                    require "views/usuario/AjustadorAdminAuthV.php";
                    //require "views/siniestros/listarSiniestrosV.php";
                break;
                
                case 'asegurado':
                    require "views/usuario/UserAuthV.php";
                break;

                default:
                    header('location: index.php?page=login');
                exit;
            }
            require "views/layouts/footer.php";
        }
 
        public static function logOut(){           
            session_unset();
            session_destroy();
            header('location:'.urlsite);
            exit;
        }        

        public function mostrarRegistro() {
         if (isset($_SESSION['id_usuario'])) {
            header('location: index.php?page=' . $_SESSION['rol_slug']);
            exit;
        }

        require "views/layouts/header.php";
        require "views/usuario/auth/RegisterV.php";
        require "views/layouts/footer.php";
        }

        public function procesarRegistro() {
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $passwordCifrada = md5($_POST['txtPassword']);
                $fotoBinaria = null;

                if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == UPLOAD_ERR_OK) {
                    $fotoBinaria = file_get_contents($_FILES['foto_perfil']['tmp_name']);
                }

                $resultado = $this->modelo->sigIn(
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
        
        public static function EditarPerfil() {
            if (!isset($_SESSION['id_usuario'])) {
                header('location:' . urlsite );
                exit;
            }
            $modelo = new UsuarioM();
            $idUsuario = $_SESSION['id_usuario'];
            $usuario = $modelo->obtenerPorID($idUsuario);
            require "views/layouts/header.php";
            require "views/layouts/navbar.php";
            require "views/usuario/EditProfileV.php";
            require "views/layouts/footer.php";
            return $usuario;
        }
        
        public function procesarEdicion() {
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btnEdit'])) {
                
                if (!isset($_SESSION['id_usuario'])) {
                    header('location: index.php?page=login');
                    exit;
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

                $resultado = $this->modelo->editarUsuario($idUsuario, $emailFinal, $passwordNueva, $alias, $fotoBinaria);


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