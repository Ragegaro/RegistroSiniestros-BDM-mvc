<?php
require "models/Conexion.php";
class LoginM{
    private $_db;
    public function __construct(){
        $this->_db=new Conexion();
    }

    public function logIn($alias,$password){
        $this->_db->conectar();
        $log = $this-> _db->conexion->prepare("SELECT * FROM usuario WHERE alias='".$alias."' AND contrasena='".$password."'");
        $log->execute();
        $this->_db->desconectar();
        if ($log->fetch (PDO::FETCH_OBJ))
            
            return true;
        else 
            return false;
    }
}