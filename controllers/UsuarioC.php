<?php 
require_once "models/UsuarioM.php";

class UsuarioC{
    private $modelo;
    
    public function __construct() {
        $this->modelo = new UsuarioM();
    }

    public function mostrarRegistro(){
        require "views/auth/RegisterV.php";
    }

    public  function procesarRegistro() {
        if ($_SERVER ['REQUEST_METHOD']=='POST'){
            /* SE HARA CON JS
            if ($_POST['txtPassword'] !== $_POST['txtPassword_Confirm']) {
                header("Location: index.php?page=sigIn&error=passwords_no_coinciden");
                exit;
            }*/
            $passwordCifrada = md5($_POST['txtPassword']);

            $resultado= $this->modelo->registrar (
                $_POST['txtNombres'],
                $_POST['txtApellidoP'],
                $_POST['txtApellidoM'],
                $_POST['dateNacimiento'],
                $_POST['genero'],
                $_POST['txtEmail'],
                $passwordCifrada,
                $_POST['txtAlias'],
                null
                //$_POST['fotos']
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
    }
?>