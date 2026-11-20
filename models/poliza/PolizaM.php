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
            return $pdo->query('SELECT id, nombre FROM aseguradoras ORDER BY nombre')->fetchAll(PDO::FETCH_OBJ);
        } finally {
            $this->_db->desconectar();
        }
    }

    /** Solo vehículos vinculados previamente a pólizas del mismo asegurado. */
    public function listarVehiculosPropios($idUsuario) {
        $pdo = $this->_db->conectar();
        try {
            $stmt = $pdo->prepare('SELECT DISTINCT v.id, v.marca, v.modelo, v.placas FROM vehiculo v INNER JOIN poliza p ON p.vehiculo_id = v.id WHERE p.usuario_id = ? ORDER BY v.marca, v.modelo');
            $stmt->execute([$idUsuario]);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } finally {
            $this->_db->desconectar();
        }
    }

    /** El alta de póliza y vehículo se confirma completa o se revierte. */
    public function registrarPolizaConVehiculo($datos, $idUsuario) {
        $pdo = $this->_db->conectar();
        try {
            $pdo->beginTransaction();

            if ($datos['aseguradora_id'] === 'nueva') {
                $stmt = $pdo->prepare('SELECT id FROM aseguradoras WHERE nombre = ? ORDER BY id LIMIT 1');
                $stmt->execute([$datos['aseguradora_nueva']]);
                $aseguradoraId = (int) $stmt->fetchColumn();
                if ($aseguradoraId === 0) {
                    $stmt = $pdo->prepare('INSERT INTO aseguradoras (nombre) VALUES (?)');
                    $stmt->execute([$datos['aseguradora_nueva']]);
                    $aseguradoraId = (int) $pdo->lastInsertId();
                }
            } else {
                $aseguradoraId = (int) $datos['aseguradora_id'];
                $stmt = $pdo->prepare('SELECT id FROM aseguradoras WHERE id = ?');
                $stmt->execute([$aseguradoraId]);
                if (!$stmt->fetchColumn()) {
                    throw new RuntimeException('La aseguradora seleccionada ya no está disponible.');
                }
            }

            if ($datos['vehiculo_modo'] === 'existente') {
                $vehiculoId = (int) $datos['vehiculo_id'];
                $stmt = $pdo->prepare('SELECT 1 FROM poliza WHERE usuario_id = ? AND vehiculo_id = ? LIMIT 1');
                $stmt->execute([$idUsuario, $vehiculoId]);
                if (!$stmt->fetchColumn()) {
                    throw new RuntimeException('El vehículo seleccionado no pertenece a tus pólizas.');
                }
            } else {
                $stmt = $pdo->prepare('INSERT INTO vehiculo (marca, modelo, color, placas, num_serie) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([
                    $datos['marca'], $datos['modelo'], $datos['color'] !== '' ? $datos['color'] : null,
                    $datos['placas'], $datos['serie']
                ]);
                $vehiculoId = (int) $pdo->lastInsertId();
            }

            $stmt = $pdo->prepare('SELECT 1 FROM poliza WHERE num_poliza = ? AND aseguradora_id = ? LIMIT 1');
            $stmt->execute([$datos['numero_poliza'], $aseguradoraId]);
            if ($stmt->fetchColumn()) {
                throw new RuntimeException('Ese número de póliza ya está registrado con la aseguradora indicada.');
            }

            $stmt = $pdo->prepare('INSERT INTO poliza (num_poliza, aseguradora_id, usuario_id, vehiculo_id) VALUES (?, ?, ?, ?)');
            $stmt->execute([$datos['numero_poliza'], $aseguradoraId, $idUsuario, $vehiculoId]);
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
