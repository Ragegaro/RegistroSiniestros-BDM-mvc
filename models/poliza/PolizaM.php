<?php 
    require_once "models/Conexion.php";
    class PolizaM {
        private $_db;

        public function __construct(){
            $this->_db = new Conexion();
        }

        public function verPolizas($idUsuario){
            $this->_db->conectar();
            $sql="call sp_verPoliza(?)";

            $stmt = $this->_db->conexion->prepare($sql);
            $stmt->execute([$idUsuario]);

            $resultados = $stmt->fetchAll(PDO::FETCH_OBJ);
            //
            $stmt->closeCursor();
            //
            $this->_db->desconectar();
            return $resultados;

        }

        public function agregarPoliza($datos){
            $this->_db->conectar();
            $sql="call sp_InsertarPoliza(?,?,?,?)";

            $stmt = $this->_db->conexion->prepare($sql);
            $stmt->execute([
                $datos['numero_poliza'],
                $datos['Aseguradora'],
                $datos['usuario_id'],
                $datos['vehiculo_id'],
            ]);

            // Cerrar el cursor para liberar recursos
            $stmt->closeCursor();
            //
            $this->_db->desconectar();
        }
    }
?>