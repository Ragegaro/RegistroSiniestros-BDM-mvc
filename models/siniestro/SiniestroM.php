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
//SE BORRARA ESTE METODO SI NO SE USA EN NINGUN LADO, SOLO ES PARA PRUEBAS

        public function listarSiniestros($idUsuario){
            $this->_db->conectar();
            $sql= "SELECT * FROM vListarSiniestros where Usuario = ?";

            $stmt = $this->_db->conexion->prepare($sql);
            $stmt->execute([$idUsuario]);

            $resultados = $stmt->fetchAll(PDO::FETCH_OBJ);
            $this->_db->desconectar();
            return $resultados;

        }
        public function listarTodosLosSiniestros(){
           $this->_db->conectar();
                
                
                $sql = "SELECT * FROM vlistarSiniestros"; 
                $stmt = $this->_db->conexion->prepare($sql);
                $stmt->execute();

                $resultado = $stmt->fetchAll(PDO::FETCH_OBJ);
                $this->_db->desconectar();
                
                return $resultado;
        }

/////REMPLAZAR POR ESTOS

/*
    public function verSiniestrosCliente($idUsuario){
        $this->_db->conectar();
        $sql = "call sp_verSiniestrosCliente(?)"; // Tu SP para clientes

        $stmt = $this->_db->conexion->prepare($sql);
        $stmt->execute([$idUsuario]);

        $resultados = $stmt->fetchAll(PDO::FETCH_OBJ);
        $stmt->closeCursor();
        $this->_db->desconectar();
        return $resultados;
    }

    // NUEVA CONSULTA PARA EL AJUSTADOR: Ve solo los que él atiende
    public function verSiniestrosAjustador($idAjustador){
        $this->_db->conectar();
        // Este procedimiento interno debe filtrar por la columna 'id_ajustador' o similar
        $sql = "call sp_verSiniestrosPorAjustador(?)"; 

        $stmt = $this->_db->conexion->prepare($sql);
        $stmt->execute([$idAjustador]);

        $resultados = $stmt->fetchAll(PDO::FETCH_OBJ);
        $stmt->closeCursor();
        $this->_db->desconectar();
        return $resultados;
    }
*/



        public function obtenerPorID($idSiniestro){
            $this->_db->conectar();
            $sql="SELECT * FROM vListarSiniestros where id = ? ";
            $stmt = $this->_db->conexion->prepare($sql);
            $stmt->execute([$idSiniestro]);

            $resultados = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->_db->desconectar();
            return $resultados;


        }

       



       
        
        public function modificar(){}

        public function asignarAjustador(){}
        
        
        
        //public function cerrarSiniestro(){}

    }   
?>