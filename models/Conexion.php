<?php

class Conexion{
    public $conexion;

    public function conectar(){
        try {
        $dsn = "mysql:host=localhost;dbname=" . DB_NAME . ";charset=utf8mb4";
            $opciones = array(
                PDO::ATTR_ERRMODE=> PDO::ERRMODE_EXCEPTION,
        //        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        
            );
            $this->conexion =new PDO($dsn,DB_USER,DB_PASS,$opciones);
         //  echo"exito";
            return $this->conexion;
        }
        catch(PDOException $e){
            echo $e->getMessage(); 

        }
    }

    public function desconectar(){
        $this->conexion=null;
    }
}