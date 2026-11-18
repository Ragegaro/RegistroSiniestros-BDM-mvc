<?php
require_once "models/Conexion.php";
require_once __DIR__ . '/MultimediaM.php';

class SiniestroM {
    private $_db;

    public function __construct() {
        $this->_db = new Conexion();
    }

    /** Reporte y evidencias se guardan juntos; una falla revierte todo el reporte. */
    public function reportarConEvidencias($datos, $idUsuario, $archivos) {
        $pdo = $this->_db->conectar();
        if (!$pdo) throw new RuntimeException('No se pudo conectar a la base de datos.');
        try {
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $pdo->beginTransaction();
            // La propiedad se comprueba también en SQL, no solo en el formulario.
            $stmt = $pdo->prepare('INSERT INTO siniestro (fecha, hora, direccion, descripcion, poliza_id)
                SELECT ?, ?, ?, ?, id FROM poliza WHERE id = ? AND usuario_id = ?');
            $stmt->execute([$datos['fecha'], $datos['hora'], $datos['ubicacion'],
                $datos['descripcion'], $datos['SelectPoliza'], (int) $idUsuario]);
            if ($stmt->rowCount() !== 1) throw new RuntimeException('Selecciona una póliza que pertenezca a tu cuenta.');
            $id = (int) $pdo->lastInsertId();
            foreach ($archivos as $archivo) MultimediaM::insertarBlob($pdo, $archivo, $id);
            $pdo->commit();
            return $id;
        } catch (Throwable $e) {
            Conexion::revertir($pdo);
            throw $e;
        } finally {
            $this->_db->desconectar();
        }
    }

    public function obtenerSiniestrosPorRol($idUsuario, $rolSlug) {
        $this->_db->conectar();
        $sql = "SELECT * FROM vListarSiniestros";
        $params = [];

        if ($rolSlug === 'ajustador') {
            $sql .= " WHERE ajustador_id = ?";
            $params[] = $idUsuario;
        } elseif ($rolSlug === 'asegurado') {
            $sql .= " WHERE Usuario = ?";
            $params[] = $idUsuario;
        } elseif (!in_array($rolSlug, ['supervisor', 'admin'], true)) {
            $sql .= " WHERE 1 = 0";
        }

        $sql .= " ORDER BY fecha DESC, id DESC";
        $stmt = $this->_db->conexion->prepare($sql);
        $stmt->execute($params);
        $resultados = $stmt->fetchAll(PDO::FETCH_OBJ);
        $this->_db->desconectar();
        return $resultados;
    }

    public function obtenerPorID($idSiniestro) {
        $this->_db->conectar();
        $sql = "SELECT * FROM vListarSiniestros WHERE id = ?";
        $stmt = $this->_db->conexion->prepare($sql);
        $stmt->execute([$idSiniestro]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->_db->desconectar();
        return $resultado;
    }

    public function obtenerMultimediaPorSiniestro($idSiniestro) {
        $this->_db->conectar();
        // El detalle no debe cargar todos los videos en memoria ni incrustarlos en base64.
        $sql = "SELECT id, tipo, ruta, COALESCE(OCTET_LENGTH(archivo), 0) AS bytes FROM multimedia WHERE siniestro_id = ? ORDER BY id";
        $stmt = $this->_db->conexion->prepare($sql);
        $stmt->execute([$idSiniestro]);
        $resultados = $stmt->fetchAll(PDO::FETCH_OBJ);
        $this->_db->desconectar();
        return $resultados;
    }

    public function obtenerAjustadoresActivos() {
        $this->_db->conectar();
        $sql = "SELECT u.id, CONCAT_WS(' ', u.nombre, u.apellido_p, NULLIF(u.apellido_m, '')) AS nombre,
            u.alias, COUNT(s.id) AS casos
            FROM usuario u INNER JOIN rol r ON r.id = u.rol_id
            LEFT JOIN siniestro s ON s.ajustador_id = u.id
            WHERE r.slug = 'ajustador' AND u.estatus = 1
            GROUP BY u.id, u.nombre, u.apellido_p, u.apellido_m, u.alias
            ORDER BY nombre, u.id";
        $resultado = $this->_db->conexion->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $this->_db->desconectar();
        return $resultado;
    }

    /** Solo admite cuentas activas del rol ajustador; 0 retira la asignación. */
    public function asignarAjustador($idSiniestro, $idAjustador) {
        $pdo = $this->_db->conectar();
        if (!$pdo) throw new RuntimeException('No se pudo conectar a la base de datos.');
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('SELECT s.id, e.nombre AS estatus FROM siniestro s
                LEFT JOIN estatus e ON e.id = s.estatus_id WHERE s.id = ? FOR UPDATE');
            $stmt->execute([(int) $idSiniestro]);
            $caso = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            if (!$caso) throw new RuntimeException('Siniestro no encontrado.');
            if (strcasecmp(trim($caso['estatus'] ?? ''), 'Cerrado') === 0) throw new RuntimeException('No se puede reasignar un siniestro cerrado.');
            if ($idAjustador > 0) {
                $stmt = $pdo->prepare("SELECT u.id FROM usuario u INNER JOIN rol r ON r.id = u.rol_id
                    WHERE u.id = ? AND r.slug = 'ajustador' AND u.estatus = 1 FOR UPDATE");
                $stmt->execute([(int) $idAjustador]);
                if (!$stmt->fetchColumn()) throw new RuntimeException('El responsable debe ser un ajustador activo.');
                $stmt->closeCursor();
            }
            $stmt = $pdo->prepare('UPDATE siniestro SET ajustador_id = ? WHERE id = ?');
            $stmt->bindValue(1, $idAjustador > 0 ? (int) $idAjustador : null, $idAjustador > 0 ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->bindValue(2, (int) $idSiniestro, PDO::PARAM_INT);
            $stmt->execute();
            $pdo->commit();
        } catch (Throwable $e) {
            Conexion::revertir($pdo);
            throw $e;
        } finally {
            $this->_db->desconectar();
        }
    }

    public function getHistorialEstatus($idSiniestro) {
        $this->_db->conectar();
        $sql = "
            SELECT h.id, h.fecha_actu, h.estatus_id, e.nombre AS estatus
            FROM historialestatus h
            INNER JOIN estatus e ON e.id = h.estatus_id
            WHERE h.siniestro_id = ?
            ORDER BY h.fecha_actu DESC, h.id DESC
        ";
        $stmt = $this->_db->conexion->prepare($sql);
        $stmt->execute([$idSiniestro]);
        $resultados = $stmt->fetchAll(PDO::FETCH_OBJ);
        $this->_db->desconectar();
        return $resultados;
    }

    public function obtenerEstatus() {
        $this->_db->conectar();
        $stmt = $this->_db->conexion->prepare("SELECT id, nombre FROM estatus ORDER BY id");
        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_OBJ);
        $this->_db->desconectar();
        return $resultados;
    }

    public function cambiarEstatus($idSiniestro, $idEstatus) {
        $this->_db->conectar();
        try {
            $this->_db->conexion->beginTransaction();

            $stmtActual = $this->_db->conexion->prepare("SELECT estatus_id FROM siniestro WHERE id = ? FOR UPDATE");
            $stmtActual->execute([$idSiniestro]);
            $actual = $stmtActual->fetchColumn();
            if ($actual === false) {
                throw new RuntimeException('Siniestro no encontrado');
            }

            if ((string) $actual !== (string) $idEstatus) {
                $stmt = $this->_db->conexion->prepare("UPDATE siniestro SET estatus_id = ? WHERE id = ?");
                $stmt->execute([$idEstatus, $idSiniestro]);

                $stmtHistorial = $this->_db->conexion->prepare(
                    "INSERT INTO historialestatus (siniestro_id, estatus_id) VALUES (?, ?)"
                );
                $stmtHistorial->execute([$idSiniestro, $idEstatus]);
            }

            $this->_db->conexion->commit();
            $resultado = true;
        } catch (Throwable $e) {
            Conexion::revertir($this->_db->conexion);
            $this->_db->desconectar();
            throw $e;
        }

        $this->_db->desconectar();
        return $resultado;
    }

    public function guardarEvaluacion($idSiniestro, $idAjustador, $diagnostico) {
        $this->_db->conectar();
        $sql = "
            INSERT INTO evaluacion (diagnostico, siniestro_id, ajustador_id)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE
                diagnostico = VALUES(diagnostico),
                ajustador_id = VALUES(ajustador_id),
                fecha_evaluacion = CURRENT_TIMESTAMP
        ";
        $stmt = $this->_db->conexion->prepare($sql);
        $resultado = $stmt->execute([$diagnostico, $idSiniestro, $idAjustador]);
        $this->_db->desconectar();
        return $resultado;
    }

    public function obtenerEvaluacion($idSiniestro) {
        $this->_db->conectar();
        $sql = "
            SELECT e.*, u.nombre AS nombre_ajustador, u.apellido_p AS apellido_ajustador
            FROM evaluacion e
            INNER JOIN usuario u ON u.id = e.ajustador_id
            WHERE e.siniestro_id = ?
            LIMIT 1
        ";
        $stmt = $this->_db->conexion->prepare($sql);
        $stmt->execute([$idSiniestro]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->_db->desconectar();
        return $resultado ?: null;
    }

    public function getDashboardAdmin() {
        $this->_db->conectar();
        $datos = [];
        $datos['resumen_estatus'] = $this->_db->conexion->query("SELECT * FROM vDashboardAdmin")->fetchAll(PDO::FETCH_ASSOC);
        $datos['ajustadores'] = $this->_db->conexion->query("SELECT * FROM vListarAjustadores")->fetchAll(PDO::FETCH_ASSOC);
        $datos['clientes'] = $this->_db->conexion->query("SELECT * FROM vListarClientes LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
        $this->_db->desconectar();
        return $datos;
    }
}
?>
