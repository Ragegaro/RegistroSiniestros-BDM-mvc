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

       /* $sql ="INSERT INTO usuario (nombre, apellido_p, apellido_m,
        nacimiento,genero, email, contrasena, alias,foto_perfil, rol_id) VALUES (?,?,?,?,?,?,?,?,?,?)";
        */
        $sql = "CALL sp_registrar_usuario(?, ?, ?, ?, ?, ?, ?, ?, ?)";
      $stmt = $this->_db->conexion->prepare($sql);
    
    $resultado = $stmt->execute([
        $nombre,
        $apellidoP,
        $apellidoM,
        $nacimiento,
        $genero,
        $email,
        $password,
        $alias,
        $fotoperfil
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