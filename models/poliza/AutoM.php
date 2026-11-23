<?php
require_once __DIR__ . '/../Conexion.php';
require_once __DIR__ . '/../Procedimientos.php';
class AutoM {
    private $_db;
    public function __construct() { $this->_db=new Conexion(); }
    public function registrarAuto($marca,$modelo,$color,$placas,$VIN) {
        $pdo=$this->_db->conectar();
        try { return Procedimientos::ejecutar($pdo,'sp_vehiculo_registrar',[$marca,$modelo,$color,$placas,$VIN]); }
        finally { $this->_db->desconectar(); }
    }
}
