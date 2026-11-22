<?php
require_once __DIR__ . '/../Conexion.php';
require_once __DIR__ . '/../../lib/Archivos.php';

class MultimediaM {
    private $db;

    private function conectar() {
        $this->db = new Conexion();
        $pdo = $this->db->conectar();
        if (!$pdo) throw new RuntimeException('No se pudo conectar a la base de datos.');
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        return $pdo;
    }

    /** Guarda fotos y videos NUEVOS como archivos físicos; solo la ruta entra a SQL. */
public static function insertarRuta(PDO $pdo, $archivo, $idSiniestro) {
    $base = dirname(__DIR__, 2);
    $idSiniestro = (int) $idSiniestro;
    $directorio = $base . '/uploads/siniestros/SIN-' . $idSiniestro;

    if (!is_dir($directorio) && !mkdir($directorio, 0755, true) && !is_dir($directorio)) {
        throw new RuntimeException('No se pudo crear la carpeta de evidencias.');
    }

    $extension = $archivo['extension'] ?? ($archivo['tipo'] === 'Video' ? 'mp4' : 'jpg');
    $extension = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $extension));
    if ($extension === '') {
        $extension = ($archivo['tipo'] === 'Video') ? 'mp4' : 'jpg';
    }

    $nombre = strtolower($archivo['tipo']) . '_' . bin2hex(random_bytes(16)) . '.' . $extension;
    $rutaRelativa = 'uploads/siniestros/SIN-' . $idSiniestro . '/' . $nombre;
    $rutaFisica = $directorio . '/' . $nombre;

    if (!move_uploaded_file($archivo['tmp'], $rutaFisica)) {
        throw new RuntimeException('No se pudo guardar la evidencia en el servidor. Revisa permisos de uploads/siniestros.');
    }

    try {
        $stmt = $pdo->prepare('INSERT INTO multimedia (tipo, archivo, ruta, siniestro_id) VALUES (?, NULL, ?, ?)');
        $stmt->execute([$archivo['tipo'], $rutaRelativa, $idSiniestro]);
        return $rutaFisica;
    } catch (Throwable $error) {
        @unlink($rutaFisica);
        throw $error;
    }
}

    /** No carga el BLOB entero: solo metadatos y la cabecera para detectar MIME. */
    public function obtener($id) {
        $pdo = $this->conectar();
        $stmt = $pdo->prepare('SELECT m.id, m.tipo, m.ruta, m.siniestro_id,
            COALESCE(OCTET_LENGTH(m.archivo), 0) AS bytes,
            SUBSTRING(m.archivo, 1, 65536) AS cabecera,
            s.ajustador_id, p.usuario_id AS Usuario
            FROM multimedia m INNER JOIN siniestro s ON s.id = m.siniestro_id
            INNER JOIN poliza p ON p.id = s.poliza_id WHERE m.id = ?');
        $stmt->execute([(int) $id]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $fila;
    }

    public function bloque($id, $inicio, $cantidad) {
        $pdo = $this->db ? $this->db->conexion : $this->conectar();
        $stmt = $pdo->prepare('SELECT SUBSTRING(archivo, ?, ?) FROM multimedia WHERE id = ?');
        $stmt->bindValue(1, (int) $inicio + 1, PDO::PARAM_INT);
        $stmt->bindValue(2, (int) $cantidad, PDO::PARAM_INT);
        $stmt->bindValue(3, (int) $id, PDO::PARAM_INT);
        $stmt->execute();
        $datos = $stmt->fetchColumn();
        $stmt->closeCursor();
        return is_resource($datos) ? stream_get_contents($datos) : $datos;
    }

    // Se mantiene lectura de BLOBs ANTIGUOS (si la base del avance 3 ya los contiene),
    // pero no existen nuevos INSERT/UPDATE de evidencias binarias.
}
