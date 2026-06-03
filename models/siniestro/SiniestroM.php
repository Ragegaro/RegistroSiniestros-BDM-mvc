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
            $idInsertado = false;
            if ($resultado) {
                $idInsertado = $this->_db->conexion->lastInsertId();
            }
            
            $this->_db->desconectar();
            return $idInsertado;
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
        
        ///////////////////////////////////////////////////////////////////////////////
        
        public function insertarMultimedia($tipo, $archivoBlob, $ruta, $idSiniestro) {
            $this->_db->conectar();
            
            $sql = "INSERT INTO multimedia (tipo, archivo, ruta, siniestro_id) VALUES (?, ?, ?, ?)";
            
            $stmt = $this->_db->conexion->prepare($sql);
            $resultado = $stmt->execute([
                $tipo, 
                $archivoBlob, 
                $ruta, 
                $idSiniestro
            ]);
            
            $this->_db->desconectar();
            return $resultado;
        }

        public function obtenerMultimediaPorSiniestro($idSiniestro) {
            $this->_db->conectar();
     
            $sql = "SELECT id, tipo, archivo, ruta FROM multimedia WHERE siniestro_id = ?";
            $stmt = $this->_db->conexion->prepare($sql);
            $stmt->execute([$idSiniestro]);
            
            $resultados = $stmt->fetchAll(PDO::FETCH_OBJ);
            $this->_db->desconectar();
            return $resultados;
        }

        public function getHistorialEstatus($idSiniestro) {
            $this->_db->conectar();
     
            $sql = "SELECT estatus_id, fecha_actu FROM historialestatus WHERE siniestro_id = ? ORDER BY fecha_actu DESC";
            $stmt = $this->_db->conexion->prepare($sql);
            $stmt->execute([$idSiniestro]);
            
            $resultados = $stmt->fetchAll(PDO::FETCH_OBJ);
            $this->_db->desconectar();
            return $resultados;
        }
/*
        public function cambiarEstatus($idSiniestro, $nuevoEstatus) {
            $this->_db->conectar();
            
            try {
                $this->_db->conexion->beginTransaction();

                // 1. Actualizar el estatus actual en la tabla principal de siniestros (si tienes esa columna)
                $sql1 = "UPDATE siniestro SET estatus = ? WHERE id = ?";
                $stmt1 = $this->_db->conexion->prepare($sql1);
                $stmt1->execute([$nuevoEstatus, $idSiniestro]);

                // 2. Insertar el registro en la bitácora
                $sql2 = "INSERT INTO historial_estatus (siniestro_id, estatus, comentario, usuario_id) VALUES (?, ?, ?, ?)";
                $stmt2 = $this->_db->conexion->prepare($sql2);
                $stmt2->execute([$idSiniestro, $nuevoEstatus, $comentario, $idUsuario]);

                $this->_db->conexion->commit();
                $resultado = true;
            } catch (Exception $e) {
                $this->_db->conexion->rollBack();
                $resultado = false;
            }

            $this->_db->desconectar();
            return $resultado;
        }*/

    }   
?>