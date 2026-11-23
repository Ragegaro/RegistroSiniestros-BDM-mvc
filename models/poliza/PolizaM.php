<?php
require_once __DIR__ . '/../Conexion.php';
require_once __DIR__ . '/../Procedimientos.php';
class PolizaM {
    private $_db;
    public function __construct() { $this->_db=new Conexion(); }
    private function filas(string $sp,array $args=[]): array {
        $pdo=$this->_db->conectar();
        try { return Procedimientos::filas($pdo,$sp,$args,PDO::FETCH_OBJ); }
        finally { $this->_db->desconectar(); }
    }
    public function verPolizas($idUsuario) { return $this->filas('sp_verPoliza',[(int)$idUsuario]); }
    public function listarAseguradoras() { return $this->filas('sp_listar_aseguradoras'); }
    public function listarVehiculosPropios($idUsuario) { return $this->filas('sp_obtener_vehiculos_usuario',[(int)$idUsuario]); }
    public function registrarPolizaConVehiculo($datos,$idUsuario) {
        $pdo=$this->_db->conectar();
        try {
            $pdo->beginTransaction();
            $id = Procedimientos::valor($pdo,'sp_poliza_registrar_completa',[
                (int)$idUsuario, $datos['numero_poliza'],
                $datos['aseguradora_id']==='nueva' ? 0 : (int)$datos['aseguradora_id'],
                $datos['aseguradora_nueva'] ?? '', $datos['vehiculo_modo'], (int)($datos['vehiculo_id']??0),
                $datos['marca']??'', $datos['modelo']??'', $datos['color']??null,
                $datos['placas']??'', $datos['serie']??''
            ]);
            if ((int)$id<=0) throw new RuntimeException('No se guardo la poliza.');
            $pdo->commit();
            return true;
        } catch(Throwable $e) { Conexion::revertir($pdo); throw $e; }
        finally { $this->_db->desconectar(); }
    }
}
