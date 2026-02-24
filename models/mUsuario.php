<?php
require "models/Conexion.php";
class mUsuario{
    private $_db;
    public function __construct(){
        $this->_db=new Conexion();
    }

    public function logIn($alias,$password){
        $this->_db->conectar();
        $query = $this-> _db->conexion->prepare("SELECT * FROM usuario WHERE alias='".$alias."' AND contrasena='".$password."'");

        $query->execute();
        $this->_db->desconectar();
        if ($query->fetch (PDO::FETCH_OBJ))
            return true;
        else 
            return false;
    }
}