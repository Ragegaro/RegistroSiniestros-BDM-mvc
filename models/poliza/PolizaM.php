<?php
require_once __DIR__ . '/../Conexion.php';

class PolizaM {
    private $_db;

    public function __construct() {
        $this->_db = new Conexion();
    }

    public function verPolizas($idUsuario) {
        $pdo = $this->_db->conectar();
        try {
            $stmt = $pdo->prepare('CALL sp_verPoliza(?)');
            $stmt->execute([$idUsuario]);
            $resultados = $stmt->fetchAll(PDO::FETCH_OBJ);
            $stmt->closeCursor();
            return $resultados;
        } finally {
            $this->_db->desconectar();
        }
    }

    public function listarAseguradoras() {
        $pdo = $this->_db->conectar();
        try {
            $stmt = $pdo->prepare('CALL sp_listar_aseguradoras()');
            $stmt->execute();
            $aseguradoras = $stmt->fetchAll(PDO::FETCH_OBJ);
            $stmt->closeCursor();
            return $aseguradoras;

        } finally {
            $this->_db->desconectar();
        }
    }

    /** Solo vehículos vinculados previamente a pólizas del mismo asegurado. */
    public function listarVehiculosPropios($idUsuario) {
        $pdo = $this->_db->conectar();
        try {
            $stmt =$pdo->prepare('CALL sp_obtener_vehiculos_usuario(?)');
            $stmt->execute([$idUsuario]);
            $autos=$stmt->fetchAll(PDO::FETCH_OBJ);
            $stmt->closeCursor();
            return $autos;
        } finally {
            $this->_db->desconectar();
        }
    }

    /** El alta de póliza y vehículo se confirma completa o se revierte. */
   public function registrarPolizaConVehiculo($datos, $idUsuario) {
        $pdo = $this->_db->conectar();
        try {
            $pdo->beginTransaction();

            // 1. Aseguradora (nueva o existente)
            if ($datos['aseguradora_id'] === 'nueva') {
                $stmt = $pdo->prepare('CALL sp_obtener_o_crear_aseguradora(?)');
                $stmt->execute([$datos['aseguradora_nueva']]);
                $aseguradoraId = (int) $stmt->fetchColumn();
                $stmt->closeCursor();

                if ($aseguradoraId <= 0) {
                    throw new RuntimeException('No se pudo obtener o registrar la aseguradora.');
                }
            } else {
                $aseguradoraId = (int) $datos['aseguradora_id'];
                $stmt = $pdo->prepare('CALL sp_validar_aseguradora_existe(?, @existe_aseg)');
                $stmt->execute([$aseguradoraId]);
                $stmt->closeCursor();

                $existeAseg = (int) $pdo->query('SELECT @existe_aseg')->fetchColumn();
                if ($existeAseg === 0) {
                    throw new RuntimeException('La aseguradora seleccionada ya no está disponible.');
                }
            }

            // 2. Vehículo (existente o nuevo)
            if ($datos['vehiculo_modo'] === 'existente') {
                $vehiculoId = (int) $datos['vehiculo_id'];
                $stmt = $pdo->prepare('CALL sp_validar_vehiculo_usuario(?, ?, @pertenece_veh)');
                $stmt->execute([$idUsuario, $vehiculoId]);
                $stmt->closeCursor();

                $perteneceVeh = (int) $pdo->query('SELECT @pertenece_veh')->fetchColumn();
                if ($perteneceVeh === 0) {
                    throw new RuntimeException('El vehículo seleccionado no pertenece a tus pólizas.');
                }
            } else {
                $color = $datos['color'] !== '' ? $datos['color'] : null;
                $stmt = $pdo->prepare('CALL sp_Registrar_vehiculo(?, ?, ?, ?, ?, @vehiculo_id)');
                $stmt->execute([
                    $datos['marca'],
                    $datos['modelo'],
                    $color,
                    $datos['placas'],
                    $datos['serie']
                ]);
                $stmt->closeCursor();

                $vehiculoId = (int) $pdo->query('SELECT @vehiculo_id')->fetchColumn();
            }

            // 3. Validar póliza duplicada
            $stmt = $pdo->prepare('CALL sp_validar_poliza_duplicada(?, ?, @poliza_dup)');
            $stmt->execute([$datos['numero_poliza'], $aseguradoraId]);
            $stmt->closeCursor();

            $polizaDuplicada = (int) $pdo->query('SELECT @poliza_dup')->fetchColumn();
            if ($polizaDuplicada > 0) {
                throw new RuntimeException('Ese número de póliza ya está registrado con la aseguradora indicada.');
            }

            // 4. Registrar la póliza
            $stmt = $pdo->prepare('CALL sp_registrar_poliza_V(?, ?, ?, ?)');
            $stmt->execute([
                $datos['numero_poliza'],
                $aseguradoraId,
                $idUsuario,
                $vehiculoId,
            ]);
            $stmt->closeCursor();

            $pdo->commit();
            return true;
        } catch (Throwable $error) {
            Conexion::revertir($pdo);
            throw $error;
        } finally {
            $this->_db->desconectar();
        }
    }
}