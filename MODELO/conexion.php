<?php
class Conexion{
    public $conexion;
    
    public function conectar(){
        try {
            $hdb= "mysql:host=localhost;dbname=".DB_NAME;//hdb =Host Data Base
            $opciones = array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            );
            $this->conexion = new PDO($hdb,DB_USER,DB_PASSWORD);
            //echo "exito";    
            return $this->conexion;
                
        }catch(PDOException $e){
            echo $e->getMessage();
        }
    }

    
    public function desconectar(){

        $this->conexion=null;
    }


}

