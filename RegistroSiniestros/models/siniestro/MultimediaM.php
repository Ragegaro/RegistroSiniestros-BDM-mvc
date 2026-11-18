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

    /** Puede participar en la misma transacción que crea el siniestro. */
    public static function insertarBlob(PDO $pdo, $archivo, $idSiniestro) {
        self::validarPaquete($pdo, $archivo['bytes']);
        $recurso = fopen($archivo['tmp'], 'rb');
        if ($recurso === false) throw new RuntimeException('No se pudo abrir la evidencia.');
        try {
            $stmt = $pdo->prepare('INSERT INTO multimedia (tipo, archivo, ruta, siniestro_id) VALUES (?, ?, NULL, ?)');
            $stmt->bindValue(1, $archivo['tipo'], PDO::PARAM_STR);
            $stmt->bindParam(2, $recurso, PDO::PARAM_LOB);
            $stmt->bindValue(3, (int) $idSiniestro, PDO::PARAM_INT);
            $stmt->execute();
        } finally {
            if (is_resource($recurso)) fclose($recurso);
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

    public function pendientes() {
        $pdo = $this->conectar();
        $videos = $pdo->query("SELECT id, siniestro_id, ruta FROM multimedia
            WHERE tipo = 'Video' AND (archivo IS NULL OR OCTET_LENGTH(archivo) = 0)
            ORDER BY id LIMIT 100")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($videos as &$video) {
            $ruta = Archivos::rutaHeredada($video['ruta']);
            $video['bytes_locales'] = $ruta ? filesize($ruta) : null;
        }
        unset($video);
        return $videos;
    }

    public function configuracion() {
        $pdo = $this->conectar();
        return $pdo->query('SELECT @@max_allowed_packet AS paquete, @@port AS puerto, DATABASE() AS base')->fetch(PDO::FETCH_ASSOC);
    }

    /** Comprueba el límite observado, sin modificar la configuración del servidor. */
    public static function validarPaquete(PDO $pdo, $bytes) {
        $stmt = $pdo->query('SELECT @@max_allowed_packet');
        $limite = (int) $stmt->fetchColumn();
        $stmt->closeCursor();
        if ($limite > 0 && $bytes + 65536 > $limite) {
            throw new RuntimeException('El archivo pesa ' . number_format($bytes / 1048576, 2) .
                ' MB y max_allowed_packet está en ' . number_format($limite / 1048576, 2) .
                ' MB. Se necesita espacio adicional para la consulta. Configura max_allowed_packet=64M en la sección [mysqld] de my.ini, reinicia MySQL y vuelve a intentar.');
        }
    }

    /** Conserva la ruta y el archivo físico como respaldo; verifica longitud y SHA-256. */
    public function migrarVideo($id) {
        $pdo = $this->conectar();
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("SELECT ruta, COALESCE(OCTET_LENGTH(archivo), 0) AS bytes
                FROM multimedia WHERE id = ? AND tipo = 'Video' FOR UPDATE");
            $stmt->execute([(int) $id]);
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            if (!$fila) throw new RuntimeException('Video no encontrado.');
            if ((int) $fila['bytes'] > 0) {
                $pdo->commit();
                return 'El video ya estaba guardado en BLOB.';
            }
            $ruta = Archivos::rutaHeredada($fila['ruta']);
            if (!$ruta) throw new RuntimeException('No se encontró el video en uploads/siniestros. Conserva o restaura la carpeta original.');
            $archivo = Archivos::inspeccionar($ruta);
            if ($archivo['tipo'] !== 'Video') throw new RuntimeException('El archivo no contiene un video admitido.');
            self::validarPaquete($pdo, $archivo['bytes']);
            $hash = hash_file('sha256', $ruta);
            $recurso = fopen($ruta, 'rb');
            if ($recurso === false) throw new RuntimeException('No se pudo abrir el video.');
            try {
                $stmt = $pdo->prepare('UPDATE multimedia SET archivo = ? WHERE id = ?');
                $stmt->bindParam(1, $recurso, PDO::PARAM_LOB);
                $stmt->bindValue(2, (int) $id, PDO::PARAM_INT);
                $stmt->execute();
            } finally {
                if (is_resource($recurso)) fclose($recurso);
            }
            $stmt = $pdo->prepare('SELECT OCTET_LENGTH(archivo) AS bytes, SHA2(archivo, 256) AS huella FROM multimedia WHERE id = ?');
            $stmt->execute([(int) $id]);
            $verificacion = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            if ((int) $verificacion['bytes'] !== $archivo['bytes'] || !hash_equals($hash, (string) $verificacion['huella'])) {
                throw new RuntimeException('La verificación del video falló; no se guardaron cambios.');
            }
            $pdo->commit();
            return 'Video convertido y verificado. Se conservó el archivo original como respaldo.';
        } catch (Throwable $e) {
            Conexion::revertir($pdo);
            throw $e;
        } finally {
            $this->db->desconectar();
        }
    }
}
