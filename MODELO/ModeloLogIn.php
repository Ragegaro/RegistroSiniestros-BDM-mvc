<?php
require "MODELO/conexion.php";

class LogIn{
    private$_db;

    public function _construct (){
        $this->_db = new Conexion();
    }

    public function LogIn($email,$password){
        $this-> _db->conectar();
        $query=$this->_db->conexion->prepare
         ("SELECT * FROM usuario WHERE email='".$email."'AND password'".$password);
        $query->execute();
        $this -> _db->desconectar();
        if ($query ->fetch(PDO::FETCH_OBJ))
            return true;
        else 
            return false;
    }
}