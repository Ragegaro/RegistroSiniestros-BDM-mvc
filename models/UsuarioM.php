<?php
require "models/Conexion.php";
class UsuarioM{
    private $_db;
    public function __construct(){
        $this->_db = new Conexion();
    }

    public function registrar(){
        $this->_db->conectar();
        $sql =" INSERT INTO
        
        "
        //Version final
        //$sql="CALL sp_registrar_Usuario()";
    }
    
    public function perfil($id){}
 
    public function editar(){}

    public function eliminar(){}
    
    

    public function editPhoto(){}

    public function listar(){}


    

}
?>