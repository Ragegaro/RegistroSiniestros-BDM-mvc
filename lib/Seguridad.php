<?php
require_once __DIR__ . '/Avisos.php';
/** Reglas compartidas por expedientes, asignaciones y descarga de evidencias. */
final class Seguridad {
    public static function esSupervisor($rol = null) {
        return in_array($rol ?? ($_SESSION['rol_slug'] ?? ''), ['supervisor', 'admin'], true);
    }

    public static function puedeVer($siniestro, $idUsuario = null, $rol = null) {
        if (!$siniestro) return false;
        $id = (int) ($idUsuario ?? ($_SESSION['id_usuario'] ?? 0));
        $rol = $rol ?? ($_SESSION['rol_slug'] ?? '');
        if ($id <= 0) return false;
        if (self::esSupervisor($rol)) return true;
        if ($rol === 'asegurado') return (int) ($siniestro['Usuario'] ?? 0) === $id;
        if ($rol === 'ajustador') return (int) ($siniestro['ajustador_id'] ?? 0) === $id;
        return false;
    }

    public static function exigirSupervisor() {
        if (empty($_SESSION['id_usuario']) || !self::esSupervisor()) {
            Avisos::paginaError('Solo el supervisor puede realizar esta operación.', 403);
        }
    }

    public static function token() {
        if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['csrf_token'];
    }

    public static function exigirPost() {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Allow: POST');
            Avisos::paginaError('Esta acción requiere enviar el formulario correspondiente.', 405);
        }
        $token = $_POST['csrf_token'] ?? null;
        if (!is_string($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            Avisos::paginaError('El formulario venció o la carga superó post_max_size de PHP. Vuelve al formulario y recarga la página.', 403);
        }
    }

    public static function texto($datos, $clave) {
        return isset($datos[$clave]) && is_string($datos[$clave]) ? trim($datos[$clave]) : '';
    }

    public static function entero($datos, $clave) {
        $valor = $datos[$clave] ?? null;
        return is_scalar($valor) ? (int) filter_var($valor, FILTER_VALIDATE_INT) : 0;
    }

    public static function escapar($valor) {
        return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
