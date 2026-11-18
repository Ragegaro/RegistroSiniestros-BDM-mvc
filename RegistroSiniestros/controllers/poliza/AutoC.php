<?php
    require_once "models/poliza/AutoM.php";
    require_once __DIR__ . '/../../lib/Seguridad.php';
    
    class AutoC{
        private  $auto;
    
        public function __construct(){
            $this->auto=new AutoM();
        }
        public function agregarAuto(){
            if (empty($_SESSION['id_usuario'])) Avisos::ir('logIn', 'error', 'Inicia sesión para registrar un vehículo.');
            Seguridad::exigirPost();
            try {
            if ($_SERVER ['REQUEST_METHOD']=='POST'){

                $resultado= $this->auto->registrarAuto(
                    $_POST['marca'],
                    $_POST['modelo'],
                    $_POST['color'],
                    $_POST['placas'],
                    $_POST['serie']
                );
                if ($resultado) {
                    Avisos::ir('agregarAuto', 'success', 'Vehículo registrado correctamente.');
                } else {
                    Avisos::ir('agregarAuto', 'error', 'No se pudo registrar el vehículo.');
                }
            }
            } catch (Throwable $e) {
                Avisos::ir('agregarAuto', 'error', Avisos::registrarError($e, 'registrar el vehículo'));
            }

        }


    }

?>
