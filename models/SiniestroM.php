<?php
    require_once "models/Conexion.php";
    class SiniestroM{
        private $_db;
        public function __construct(){
            $this->_db = new Conexion();
        }


        public function reportar($fecha,$hora,$direccion,$descripcion=null,$poliza){
            $this->_db->conectar();

            $sql="INSERT INTO siniestro (fecha, hora, direccion,descripcion,poliza_id)
            values (?,?,?,?,?)";
            $stmt = $this-> _db->conexion->prepare($sql);

            $resultado = $stmt->execute ([
                $fecha,
                $hora,
                $direccion,
                $descripcion,
                $poliza

            ]);
            $this->_db->desconectar();
            return $resultado;
        }

        public function listarSiniestros($idUsuario){
            $this->_db->conectar();
            $sql= "SELECT * FROM vListarSiniestros where Usuario = ?";

            $stmt = $this->_db->conexion->prepare($sql);
            $stmt->execute([$idUsuario]);

            $resultados = $stmt->fetchAll(PDO::FETCH_OBJ);
            $this->_db->desconectar();
            return $resultados;

        }

        public function obtenerPorID($idSiniestro){
            $this->_db->conectar();
            $sql="SELECT * FROM vListarSiniestros where id = ? ";
            $stmt = $this->_db->conexion->prepare($sql);
            $stmt->execute([$idSiniestro]);

            $resultados = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->_db->desconectar();
            return $resultados;


        }



        public function listarTodosLosSiniestros() {
            $this->_db->conectar();
            
            
            $sql = "SELECT * FROM vlistarSiniestros"; 
            $stmt = $this->_db->conexion->prepare($sql);
            $stmt->execute();

            $resultado = $stmt->fetchAll(PDO::FETCH_OBJ);
            $this->_db->desconectar();
            
            return $resultado;
        }
        
        public function modificar(){}

        public function asignarAjustador(){}
        
        
        
        //public function cerrarSiniestro(){}

    }   
?>