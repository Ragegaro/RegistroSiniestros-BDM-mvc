<?php
    require_once "models/PolizaM.php";

    class PolizaC{

        public static function listarPolizas($id = null){
           $modelo = new PolizaM();
            
            $id_final = ($id !== null) ? $id : (isset($_SESSION['id_usuario']) 
            ? $_SESSION['id_usuario'] : null);
            if ($id_final===null){
                return [];
            }

            return $modelo->verPolizas($id_final);
        }
    }
?>