<?php
require "models/Conexion.php";
class SiniestroM{
    private $_db;
    public function __construct(){
        $this->_db = new Conexion();
    }



    public function reportar(){}
    
    public function modificar(){}

    public function asignarAjustador(){}
    
    public function listar(){}
    
    //public function cerrarSiniestro(){}


    

}
?>