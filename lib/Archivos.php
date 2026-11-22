<?php
/** Validación de archivos, rutas heredadas y rangos para reproducir video BLOB. */
final class Archivos {
    const MAX_BYTES = 25 * 1024 * 1024;
    const MAX_TOTAL_BYTES = 50 * 1024 * 1024;
    const MAX_ARCHIVOS = 10;
    const BLOQUE_BYTES = 1024 * 1024;

    public static function tiposPermitidos() {
        return [
            'image/jpeg' => 'Foto', 'image/png' => 'Foto',
            'image/webp' => 'Foto', 'image/gif' => 'Foto',
            'video/mp4' => 'Video', 'application/mp4' => 'Video',
            'video/webm' => 'Video', 'video/quicktime' => 'Video',
            'video/ogg' => 'Video'
        ];
    }

    public static function inspeccionar($ruta) {
        if (!is_file($ruta) || !is_readable($ruta)) throw new RuntimeException('No se pudo leer la evidencia.');
        $bytes = filesize($ruta);
        if (!$bytes || $bytes > self::MAX_BYTES) throw new RuntimeException('Cada evidencia debe pesar entre 1 byte y 25 MB.');
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($ruta);
        $permitidos = self::tiposPermitidos();
        if (!isset($permitidos[$mime])) throw new RuntimeException('Formato no admitido. Usa JPG, PNG, WEBP, GIF, MP4, WEBM, MOV u OGV.');
        return ['tmp' => $ruta, 'tipo' => $permitidos[$mime], 'bytes' => $bytes];
    }

    public static function validarCarga($archivos) {
        if ($archivos === null) return [];
        if (!is_array($archivos) || !isset($archivos['error']) || !is_array($archivos['error'])) {
            throw new RuntimeException('La carga de evidencias no tiene un formato válido.');
        }
        if (count($archivos['error']) > self::MAX_ARCHIVOS) throw new RuntimeException('Adjunta como máximo 10 evidencias.');
        $resultado = [];
        $total = 0;
        foreach ($archivos['error'] as $i => $error) {
            if ($error === UPLOAD_ERR_NO_FILE) continue;
            if ($error !== UPLOAD_ERR_OK) throw new RuntimeException('No se completó una carga. Revisa upload_max_filesize y post_max_size en PHP.');
            $tmp = $archivos['tmp_name'][$i] ?? null;
            if (!is_string($tmp) || !is_uploaded_file($tmp)) throw new RuntimeException('La evidencia no proviene de una carga válida.');
            $archivo = self::inspeccionar($tmp);
            $total += $archivo['bytes'];
            if ($total > self::MAX_TOTAL_BYTES) throw new RuntimeException('Las evidencias juntas no deben superar 50 MB.');
            $resultado[] = $archivo;
        }
        return $resultado;
    }

    /** Solo permite archivos reales dentro de uploads/siniestros; nunca URLs. */
    public static function rutaHeredada($ruta) {
        if (!is_string($ruta) || strpos($ruta, "\0") !== false) return false;
        $normalizada = str_replace('\\', '/', $ruta);
        if (strpos($normalizada, 'uploads/siniestros/') !== 0) return false;
        $raiz = realpath(dirname(__DIR__) . '/uploads/siniestros');
        $real = realpath(dirname(__DIR__) . '/' . $normalizada);
        if (!$raiz || !$real || !is_file($real) || !is_readable($real)) return false;
        $prefijo = $raiz . DIRECTORY_SEPARATOR;
        return strncmp($real, $prefijo, strlen($prefijo)) === 0 ? $real : false;
    }

    /** Admite un rango HTTP: bytes=0-99, bytes=100- o bytes=-100. */
    public static function rango($cabecera, $total) {
        if ($total <= 0) throw new InvalidArgumentException('Archivo vacío.');
        if ($cabecera === '' || $cabecera === null) return [0, $total - 1, 200];
        if (!is_string($cabecera) || !preg_match('/^bytes=(\d*)-(\d*)$/D', trim($cabecera), $m) || ($m[1] === '' && $m[2] === '')) {
            throw new InvalidArgumentException('Rango no válido.');
        }
        if ($m[1] === '') {
            $cantidad = (int) $m[2];
            if ($cantidad <= 0) throw new InvalidArgumentException('Rango no válido.');
            return [max(0, $total - $cantidad), $total - 1, 206];
        }
        $inicio = (int) $m[1];
        $fin = $m[2] === '' ? $total - 1 : min((int) $m[2], $total - 1);
        if ($inicio >= $total || $fin < $inicio) throw new InvalidArgumentException('Rango fuera del archivo.');
        return [$inicio, $fin, 206];
    }
}
