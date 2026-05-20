<?php 
    require_once "models/Conexion.php";
    class PolizaM {
        private $_db;

        public function __construct(){
            $this->_db = new Conexion();
        }

        public function verPolizas($idUsuario){
            $this->_db->conectar();
            $sql="SELECT * FROM  vPolizas where Usuario = ?";

            $stmt = $this->_db->conexion->prepare($sql);
            $stmt->execute([$idUsuario]);

            $resultados = $stmt->fetchAll(PDO::FETCH_OBJ);
            $this->_db->desconectar();
            return $resultados;

        }
    }
?>