<?php
require "models/Conexion.php";
class UsuarioM{
    private $_db;
    public function __construct(){
        $this->_db = new Conexion();
    }

    public function registrar($nombre,$apellidoP,$apellidoM = null,
        $nacimiento,$genero =null,$email,$password,$alias,$fotoperfil =null){

        $this->_db->conectar();

        $sql ="INSERT INTO usuario (nombre, apellido_p, apellido_m,
        nacimiento,genero, email, contrasena, alias,foto_perfil, rol_id) VALUES (?,?,?,?,?,?,?,?,?,?)";
        
        $stmt = $this->_db->conexion->prepare($sql);
        
        $resultado=$stmt -> execute([
            $nombre,
            $apellidoP,
            $apellidoM,
            $nacimiento,
            $genero,
            $email,
            $password,
            $alias,
            $fotoperfil,
            3
            ]);
           
        $this->_db->desconectar();
        
        return $resultado;
        


        //Version final
        //$sql="CALL sp_registrar_Usuario(:nombre,:_apellidosP, ApellidoM....)";
    }
    
    public function perfil($id){}
 
    public function editar(){}

    public function eliminar(){}
    
    

    public function editPhoto(){}

    public function listar(){}


    

}
?>