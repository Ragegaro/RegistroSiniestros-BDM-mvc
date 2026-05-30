<?php
require_once "models/Conexion.php";
class UsuarioM{
    private $_db;
    public function __construct(){
        $this->_db = new Conexion();
    }

    public function sigIn($nombre,$apellidoP,$apellidoM = null,
        $nacimiento,$genero =null,$email,$password,$alias,$fotoperfil =null)
        {

            $this->_db->conectar();

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
    
    public function logIn($alias,$password){
        $this->_db->conectar();

        $sql ="CALL sp_validar_usuario(?,?)";

        $stmt=$this->_db->conexion->prepare($sql);
        $stmt->execute([$alias,$password]);

        $usuario=$stmt->fetch(PDO::FETCH_OBJ); 

        $this->_db->desconectar();
        if ($usuario)
            return $usuario;
        else 
            return false;
    }

    public function obtenerPorID($idUsuario){
       
        $this->_db->conectar();
        $sql = "SELECT nombre, apellido_p, apellido_m, nacimiento, genero, email, alias, foto_perfil, rol_id 
                FROM usuario 
                WHERE id = ?";
        
        $stmt = $this->_db->conexion->prepare($sql);
        $stmt->execute([$idUsuario]);

        $resultado = $stmt->fetch(PDO::FETCH_OBJ);
        $this->_db->desconectar();
        
        return $resultado;
    }
 
    public function editarUsuario($id, $email, $password, $alias, $foto) {
    $this->_db->conectar();
    
    // CASO 1: Cambió Contraseña Y Cambió Foto
    if ($password !== null && $foto !== null) {
        $sql = "UPDATE usuario SET email = ?, alias = ?, contrasena = ?, foto_perfil = ? WHERE id = ?";
        $stmt = $this->_db->conexion->prepare($sql);
        $resultado = $stmt->execute([$email, $alias, $password, $foto, $id]);
    } 
    // CASO 2: Cambió Contraseña pero NO la Foto
    else if ($password !== null && $foto === null) {
        $sql = "UPDATE usuario SET email = ?, alias = ?, contrasena = ? WHERE id = ?";
        $stmt = $this->_db->conexion->prepare($sql);
        $resultado = $stmt->execute([$email, $alias, $password, $id]);
    } 
    // CASO 3: NO cambió contraseña pero SÍ la Foto 
    else if ($password === null && $foto !== null) {
        $sql = "UPDATE usuario SET email = ?, alias = ?, foto_perfil = ? WHERE id = ?";
        $stmt = $this->_db->conexion->prepare($sql);
        $resultado = $stmt->execute([$email, $alias, $foto, $id]);
    } 
    // CASO 4: Solo cambió texto (Email o Alias), mantiene foto y contraseña iguales
    else {
        $sql = "UPDATE usuario SET email = ?, alias = ? WHERE id = ?";
        $stmt = $this->_db->conexion->prepare($sql);
        $resultado = $stmt->execute([$email, $alias, $id]);
    }
    
    $this->_db->desconectar();
    return $resultado;
    }

    
    public function eliminar(){}
    
    



    public function listar(){}


    
}
?>