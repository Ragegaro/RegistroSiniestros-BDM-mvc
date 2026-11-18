<?php
/** Mensajes de una sola lectura: viajan en la sesión, nunca en la URL. */
final class Avisos {
    public static function poner($tipo, $texto) {
        $tipo = in_array($tipo, ['error', 'success', 'info'], true) ? $tipo : 'info';
        if (!isset($_SESSION['avisos'])) $_SESSION['avisos'] = [];
        $_SESSION['avisos'][] = ['tipo' => $tipo, 'texto' => (string) $texto];
        $_SESSION['avisos'] = array_slice($_SESSION['avisos'], -5);
    }

    public static function extraer() {
        $avisos = $_SESSION['avisos'] ?? [];
        unset($_SESSION['avisos']);
        return is_array($avisos) ? $avisos : [];
    }

    public static function ir($pagina, $tipo = null, $texto = '', $parametros = []) {
        if ($tipo !== null) self::poner($tipo, $texto);
        $pagina = preg_match('/^[a-zA-Z0-9_]+$/D', $pagina) ? $pagina : 'logIn';
        $query = ['page' => $pagina];
        if (isset($parametros['id'])) $query['id'] = max(0, (int) $parametros['id']);
        if (isset($parametros['filtro']) && in_array($parametros['filtro'], ['todos', 'pendientes'], true)) $query['filtro'] = $parametros['filtro'];
        header('Location: index.php?' . http_build_query($query), true, ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' ? 303 : 302);
        exit;
    }

    /** Solo mensajes controlados; jamás mostrar SQL, datos binarios o trazas de PDO. */
    public static function mensajeError(Throwable $error, $accion = 'completar la operación') {
        if ($error instanceof PDOException) {
            $numero = (int) ($error->errorInfo[1] ?? 0);
            $estado = (string) ($error->errorInfo[0] ?? $error->getCode());
            $estado = preg_match('/^[A-Z0-9]{5}$/D', $estado) ? $estado : 'N/D';
            $mensajes = [
                1062 => 'Ya existe un registro con alguno de esos datos. Revisa los campos que deben ser únicos.',
                1452 => 'Falta un registro relacionado en la base de datos. Revisa los catálogos y la selección del formulario.',
                1153 => 'MySQL rechazó un paquete demasiado grande. Revisa max_allowed_packet en my.ini; para estos videos se recomienda 64M.',
                2020 => 'El paquete supera el límite del cliente MySQL. Revisa su configuración de tamaño.',
                2006 => 'Se perdió la conexión con MySQL. Puede deberse al tamaño del archivo, un reinicio o un tiempo de espera.',
                2013 => 'Se perdió la conexión mientras MySQL procesaba la operación.',
                2002 => 'No se pudo conectar con MySQL. Comprueba que esté iniciado y que el puerto de config.php sea correcto.',
                1045 => 'MySQL rechazó la conexión. Revisa el usuario y la contraseña configurados para la base.',
                1049 => 'La base de datos configurada no existe en este servidor.',
                1146 => 'Falta una tabla o vista de la aplicación. Revisa la instalación de la base.',
                1054 => 'Una columna no coincide con el esquema esperado por la aplicación.',
                1305 => 'Falta un procedimiento de la aplicación en la base de datos.'
            ];
            $mensaje = $mensajes[$numero] ?? ('No se pudo ' . $accion . '. Revisa el registro de errores de Apache/PHP.');
            return $mensaje . ' [MySQL ' . $numero . '; SQLSTATE ' . $estado . ']';
        }
        // RuntimeException se usa en los validadores de esta aplicación.
        if ($error instanceof RuntimeException) return $error->getMessage();
        return 'No se pudo ' . $accion . '. Revisa el registro de errores de Apache/PHP.';
    }

    public static function registrarError(Throwable $error, $accion) {
        // No registrar getMessage(): puede contener correo, contraseña, SQL o bytes.
        $codigo = $error instanceof PDOException ? (int) ($error->errorInfo[1] ?? 0) : $error->getCode();
        error_log($accion . ': ' . get_class($error) . ' código=' . $codigo . ' archivo=' . basename($error->getFile()) . ' línea=' . $error->getLine());
        return self::mensajeError($error, $accion);
    }

    public static function paginaError($mensaje, $estado = 500) {
        while (ob_get_level() > 0) ob_end_clean();
        http_response_code($estado);
        header('Content-Type: text/html; charset=UTF-8');
        self::poner('error', $mensaje);
        require __DIR__ . '/../views/layouts/header.php';
        if (!empty($_SESSION['id_usuario'])) require __DIR__ . '/../views/layouts/navbar.php';
        require __DIR__ . '/../views/layouts/error.php';
        require __DIR__ . '/../views/layouts/footer.php';
        exit;
    }

    public static function excepcionNoControlada(Throwable $error) {
        $mensaje = self::registrarError($error, 'completar la operación');
        if (($_GET['page'] ?? '') === 'chatMensajes' || (($_GET['page'] ?? '') === 'chatEnviar' && strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false)) {
            while (ob_get_level() > 0) ob_end_clean();
            http_response_code(500);
            header('Content-Type: application/json; charset=UTF-8');
            header('Cache-Control: private, no-store, max-age=0');
            header('X-Content-Type-Options: nosniff');
            echo json_encode(['ok' => false, 'error' => 'No se pudo completar la operación del chat. Inténtalo de nuevo.']);
            exit;
        }
        if (($_GET['page'] ?? '') === 'evidencia' || ($_SERVER['REQUEST_METHOD'] ?? '') === 'HEAD') {
            while (ob_get_level() > 0) ob_end_clean();
            http_response_code(500);
            exit;
        }
        self::paginaError($mensaje);
    }

    /** Limpia enlaces antiguos sin reflejar mensajes arbitrarios recibidos por GET. */
    public static function limpiarUrlAntigua($pagina) {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') return;
        if (!in_array($pagina, ['logIn', 'login', 'sigIn', 'miPerfil', 'report', 'detalle'], true)) return;
        if (!isset($_GET['msg']) && !isset($_GET['error']) && !isset($_GET['success'])) return;
        if ($pagina === 'login') $pagina = 'logIn';
        $error = isset($_GET['msg']) || isset($_GET['error']);
        $texto = $error ? 'No se pudo completar la operación. Revisa los datos e inténtalo nuevamente.' : 'Operación completada correctamente.';
        if ($error && $pagina === 'logIn') $texto = 'Alias o contraseña incorrectos. Inténtalo nuevamente.';
        if ($pagina === 'sigIn' && ($_GET['error'] ?? '') === 'password') $texto = 'Las contraseñas no coinciden.';
        self::ir($pagina, $error ? 'error' : 'success', $texto, ['id' => $_GET['id'] ?? null]);
    }
}
