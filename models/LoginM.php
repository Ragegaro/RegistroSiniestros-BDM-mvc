<?php
require "models/Conexion.php";
class LoginM{
    private $_db;
    
    public function __construct(){
        $this->_db=new Conexion();
    }

    public function logIn($alias,$password){
        $this->_db->conectar();
       // Usando SELECT (para pruebas)
        $sql = "SELECT * FROM usuario WHERE alias = ? AND contrasena = ?";
        //Version Final 
        //$sql ="CALL sp_validar_usuario(?,?)";

        $stmt=$this->_db->conexion->prepare($sql);

        $stmt->execute([$alias,$password]);
        $usuario=$stmt->fetch(PDO::FETCH_OBJ); 
        

        $this->_db->desconectar();
        if ($usuario)
            return $usuario;
        else 
            return false;
    }
}
?>
