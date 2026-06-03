<?php
    require_once "models/poliza/PolizaM.php";

    class PolizaC{
        private $modelo;
        
        public function __construct() {
            $this->modelo = new PolizaM();
        }

        public  function listarPolizas(){

            if (!isset($_SESSION['id_usuario'])) {
                header("Location: ?page=login");
                exit();
            }
           $idUsuario = $_SESSION['id_usuario'];
           $misPolizas = $this->modelo->verPolizas($idUsuario);

           $misPolizas = is_array($misPolizas) ? $misPolizas : [];
           require "views/layouts/header.php";   
           require "views/layouts/navbar.php";
           require "views/polizas/polizasV.php";
           require "views/layouts/footer.php";
          //  return $modelo->verPolizas($id_final);

        }



        /*public function mostrarFormularioAgregar() {
            if (!isset($_SESSION['usuario_id'])) {
                header("Location: ?page=login");
                exit();
            }

            require_once 'views/header.php';
            require_once 'views/agregarPolizaV.php'; 
            require_once 'views/footer.php';
        }

        
        public function guardarPoliza() {
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $idUsuario = $_SESSION['usuario_id'];
                $numeroPoliza = $_POST['numero_poliza'];
                $marca = $_POST['marca'];
                $modelo = $_POST['modelo'];

                $modeloPoliza = new PolizaM();
                $exito = $modeloPoliza->insertarPoliza($idUsuario, $numeroPoliza, $marca, $modelo);

                if ($exito) {
                    header("Location: ?page=polizas&status=success");
                } else {
                    header("Location: ?page=agregarPoliza&status=error");
                }
                exit();
            }
        }*/
  }
?>