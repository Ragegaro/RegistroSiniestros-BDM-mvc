<?php
require_once __DIR__ . '/../../models/siniestro/MultimediaM.php';
require_once __DIR__ . '/../../lib/Seguridad.php';

class MultimediaC {
    /** Sirve evidencia autorizada, con rangos HTTP para avanzar/retroceder videos. */
    public function ver() {
        if (!in_array($_SERVER['REQUEST_METHOD'] ?? '', ['GET', 'HEAD'], true)) {
            header('Allow: GET, HEAD');
            http_response_code(405);
            exit;
        }
        if (empty($_SESSION['id_usuario'])) {
            http_response_code(403);
            exit;
        }
        try {
            $modelo = new MultimediaM();
            $media = $modelo->obtener(Seguridad::entero($_GET, 'id'));
            if (!$media || !Seguridad::puedeVer($media)) {
                http_response_code(404);
                exit;
            }
            $ruta = false;
            $total = (int) $media['bytes'];
            $cabecera = $media['cabecera'];
            if (is_resource($cabecera)) $cabecera = stream_get_contents($cabecera);
            if ($total === 0) {
                $ruta = Archivos::rutaHeredada($media['ruta']);
                if (!$ruta) {
                    http_response_code(404);
                    exit;
                }
                $total = (int) filesize($ruta);
                $cabecera = file_get_contents($ruta, false, null, 0, 65536);
            }
            $mime = (new finfo(FILEINFO_MIME_TYPE))->buffer((string) $cabecera);
            $permitidos = Archivos::tiposPermitidos();
            if (!isset($permitidos[$mime]) || $permitidos[$mime] !== $media['tipo']) {
                http_response_code(415);
                exit;
            }
            try {
                list($inicio, $fin, $estado) = Archivos::rango($_SERVER['HTTP_RANGE'] ?? '', $total);
            } catch (InvalidArgumentException $e) {
                http_response_code(416);
                header('Content-Range: bytes */' . $total);
                header('Content-Length: 0');
                exit;
            }
            // Evita bloquear otras pestañas mientras se reproduce el video.
            session_write_close();
            if ($mime === 'application/mp4') $mime = 'video/mp4';
            http_response_code($estado);
            header('Content-Type: ' . $mime);
            header('X-Content-Type-Options: nosniff');
            header('Cache-Control: private, no-store');
            header('Accept-Ranges: bytes');
            header('Content-Length: ' . ($fin - $inicio + 1));
            if ($estado === 206) header('Content-Range: bytes ' . $inicio . '-' . $fin . '/' . $total);
            if ($_SERVER['REQUEST_METHOD'] === 'HEAD') exit;
            if (function_exists('ini_set')) ini_set('zlib.output_compression', '0');
            while (ob_get_level() > 0) ob_end_clean();

            if ($ruta) {
                $recurso = fopen($ruta, 'rb');
                if (!$recurso) throw new RuntimeException('No se pudo abrir la evidencia.');
                try {
                    fseek($recurso, $inicio);
                    for ($pos = $inicio; $pos <= $fin && !connection_aborted();) {
                        $datos = fread($recurso, min(Archivos::BLOQUE_BYTES, $fin - $pos + 1));
                        if ($datos === false || $datos === '') break;
                        echo $datos;
                        $pos += strlen($datos);
                        flush();
                    }
                } finally {
                    fclose($recurso);
                }
            } else {
                for ($pos = $inicio; $pos <= $fin && !connection_aborted();) {
                    $datos = $modelo->bloque($media['id'], $pos, min(Archivos::BLOQUE_BYTES, $fin - $pos + 1));
                    if (!is_string($datos) || $datos === '') throw new RuntimeException('La evidencia está incompleta.');
                    echo $datos;
                    $pos += strlen($datos);
                    flush();
                }
            }
        } catch (Throwable $e) {
            Avisos::registrarError($e, 'leer la evidencia');
            if (!headers_sent()) {
                header_remove('Content-Length');
                header_remove('Content-Range');
                http_response_code(500);
            }
        }
        exit;
    }

}
