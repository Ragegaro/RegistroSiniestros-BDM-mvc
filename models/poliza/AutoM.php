<?php
require "models/Conexion.php";
class AutoM{
    private $_db;
    public function __construct(){
        $this->_db = new Conexion();
    }

    public function registrarAuto($marca, $modelo, $color = null, $placas, $VIN){

        $this->_db->conectar();

        $sql ="INSERT INTO vehiculo (marca, modelo,color,
        placas,num_serie) VALUES (?,?,?,?,?)";

        //$sql = "CALL sp_registrar_auto(?, ?, ?, ?, ?)";
        $stmt = $this->_db->conexion->prepare($sql);
        
        $resultado = $stmt->execute([
            $marca, $modelo,
            $color,
            $placas, $VIN
        ]);
        
        $this->_db->desconectar();
        
        return $resultado;
    }
    
    public function perfil($id){}
 
    public function editar(){}

    public function eliminar(){}
    
    

    public function editPhoto(){}

    public function listar(){}


    

}
?>