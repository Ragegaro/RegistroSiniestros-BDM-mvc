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
/*
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
*/

        public function obtenerSiniestrosPorRol($idUsuario, $rolSlug) {
            $this->_db->conectar();
            
            //es una vista planeada para traer todos esos datos, por eso el *
            $sql = "SELECT * FROM vListarSiniestros";
            $params = [];

            
            if ($rolSlug === 'ajustador') {
                $sql .= " WHERE ajustador_id = ?";
                $params[] = $idUsuario;
                
            } elseif ($rolSlug === 'asegurado') {
                $sql .= " WHERE Usuario = ?"; 
                $params[] = $idUsuario;
            }
            

            $stmt = $this->_db->conexion->prepare($sql);
            $stmt->execute($params);
            
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
     
            $sql="Select * From vHistorialEstatus where siniestro_id = ? ";
            //$sql = "SELECT estatus_id, fecha_actu FROM historialestatus WHERE siniestro_id = ? ORDER BY fecha_actu DESC";
            $stmt = $this->_db->conexion->prepare($sql);
            $stmt->execute([$idSiniestro]);
            
            $resultados = $stmt->fetchAll(PDO::FETCH_OBJ);
            $this->_db->desconectar();
            return $resultados;
        }
        
    

        public function obtenerTodosEstatus() {
            $this->_db->conectar();
            $sql = "SELECT id, nombre FROM estatus";
            $stmt = $this->_db->conexion->prepare($sql);
            $stmt->execute();
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->_db->desconectar();
            return $resultados;
        }

        
        public function getDashboardAdmin() {
            $this->_db->conectar();
            $datos = [];
            
        
            $stmt1 = $this->_db->conexion->query("SELECT * FROM vDashboardAdmin");
            $datos['resumen_estatus'] = $stmt1->fetchAll(PDO::FETCH_ASSOC);
            
        
            $stmt2 = $this->_db->conexion->query("SELECT * FROM vListarAjustadores");
            $datos['ajustadores'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);
            
        
            $stmt3 = $this->_db->conexion->query("SELECT * FROM vListarClientes LIMIT 10");
            $datos['clientes'] = $stmt3->fetchAll(PDO::FETCH_ASSOC);

            $this->_db->desconectar();
            return $datos;
        }

        public function actualizarDiagnostico($idSiniestro, $diagnostico) {
            $this->_db->conectar();
            
            $sql = "UPDATE siniestro SET descripcion = ? WHERE id = ?"; 
            $stmt = $this->_db->conexion->prepare($sql);
            $resultado = $stmt->execute([$diagnostico, $idSiniestro]);
            $this->_db->desconectar();
            return $resultado;
        }


        public function cambiarEstatus($idSiniestro, $nuevoEstatus) {
            $this->_db->conectar();
            $sql = "UPDATE siniestro SET estatus_id = ? WHERE id = ?";
            $stmt = $this->_db->conexion->prepare($sql);
            $resultado = $stmt->execute([$nuevoEstatus, $idSiniestro]);
            $this->_db->desconectar();
            return $resultado;
        }

    }   
?>