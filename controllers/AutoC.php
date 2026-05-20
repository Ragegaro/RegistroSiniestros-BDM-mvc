<?php
    require_once "models/AutoM.php";
    
    class AutoC{
        private  $auto;
    
        public function __construct(){
            $this->auto=new AutoM();
        }
        public function agregarAuto(){
             if ($_SERVER ['REQUEST_METHOD']=='POST'){

            $resultado= $this->auto->registrarAuto(
                $_POST['marca'],
                $_POST['modelo'],
                $_POST['color'],
                $_POST['placas'],
                $_POST['serie']
            );

                if ($resultado) {
                    header("Location: " . urlsite . "?page=asegurado");
                    exit;
                } else {
                    echo "Error al guardar el auto.";
                    exit;
                }
            }    

        }


    }

?>