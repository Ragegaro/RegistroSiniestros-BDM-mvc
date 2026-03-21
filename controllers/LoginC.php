<?php
    session_start();
    require "models/LoginM.php";

    class loginController{
        public static function index(){
            if (isset($_SESSION['alias'])) header('location:'.urlsite);                   
            require "views/layouts/header.php";
            require "views/auth/LoginV.php";
            require "views/layouts/footer.php";
            exit;                    
        }
        
        public static function login(){
            
            $_modelo = new LoginM();
            $_alias = trim($_POST ['txtalias']);
            $_pass = md5(trim($_POST['txtpassword']));
            $_usuario = $_modelo->logIn($_alias,$_pass);

            if ($_usuario){
                $_SESSION['alias'] = $_usuario->alias;
                $_SESSION['rol_id']= $_usuario->rol_id;

                switch ($_SESSION['rol_id']){
                    case '1'://rol_id=supervisor
                        header('location:'.urlsite."?page=supervisor");
                    break;

                    case '2'://rol_id=ajustador
                        header('location:'.urlsite."?page=supervisor");
                    break;
                    
                    case '3'://rol_id=asegurado
                        header('location:'.urlsite."?page=asegurado");
                    break;
                    default:
                    header('location:'.urlsite."?msg=No coinciden las credenciales");
                }
                exit;
            }           
        }   

        
        public static function logout(){
            session_start();
            session_unset();
            session_destroy();
            header('location:'.urlsite);
            exit;
        }
    }