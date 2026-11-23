<?php
require_once __DIR__ . '/../Conexion.php';
require_once __DIR__ . '/../Procedimientos.php';
class UsuarioM {
    private $_db;
    public function __construct() { $this->_db=new Conexion(); }
    public function sigIn($nombre,$apellidoP,$apellidoM,$nacimiento,$genero,$email,$password,$alias,$fotoperfil) {
        $pdo=$this->_db->conectar();
        try { return Procedimientos::ejecutar($pdo,'sp_registrar_usuario',[
            $nombre,$apellidoP,$apellidoM,$nacimiento,$genero,$email,$password,$alias,$fotoperfil
        ]); } finally { $this->_db->desconectar(); }
    }
    public function logIn($alias,$password) {
        $pdo=$this->_db->conectar();
        try { return Procedimientos::fila($pdo,'sp_validar_usuario',[$alias,$password],PDO::FETCH_OBJ) ?: false; }
        finally { $this->_db->desconectar(); }
    }
    public function obtenerPorID($idUsuario) {
        $pdo=$this->_db->conectar();
        try { return Procedimientos::fila($pdo,'sp_usuario_obtener',[(int)$idUsuario],PDO::FETCH_OBJ); }
        finally { $this->_db->desconectar(); }
    }
    public function editarUsuario($id,$email,$password,$alias,$foto) {
        $pdo=$this->_db->conectar();
        try { return Procedimientos::ejecutar($pdo,'sp_usuario_editar',[
            (int)$id,$email,$alias,$password,$foto,$password!==null ? 1 : 0,$foto!==null ? 1 : 0
        ]); } finally { $this->_db->desconectar(); }
    }
    public function getDashboardAdmin() {
        $pdo=$this->_db->conectar();
        try { return [
            'resumen_estatus'=>Procedimientos::filas($pdo,'sp_dashboard_estados'),
            'ajustadores'=>Procedimientos::filas($pdo,'sp_dashboard_ajustadores'),
            'clientes'=>Procedimientos::filas($pdo,'sp_dashboard_clientes')
        ]; } finally { $this->_db->desconectar(); }
    }
}
