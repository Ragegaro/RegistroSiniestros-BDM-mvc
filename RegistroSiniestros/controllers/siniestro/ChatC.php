<?php
require_once __DIR__ . '/../../models/siniestro/ChatM.php';

class ChatC {
    private static function json($datos, $estado = 200) {
        while (ob_get_level() > 0) ob_end_clean();
        http_response_code($estado);
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: private, no-store, max-age=0');
        header('X-Content-Type-Options: nosniff');
        echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        exit;
    }

    private static function usuario() {
        $id = (int) ($_SESSION['id_usuario'] ?? 0);
        if ($id <= 0) throw new ChatError('Tu sesión terminó. Vuelve a iniciar sesión para usar el chat.', 401);
        return $id;
    }

    private static function entero($datos, $clave, $minimo = 0) {
        $valor = $datos[$clave] ?? 0;
        $numero = is_scalar($valor) ? filter_var($valor, FILTER_VALIDATE_INT) : false;
        if ($numero === false || $numero < $minimo || $numero > 4294967295) {
            throw new ChatError('La solicitud del chat no es válida. Recarga el expediente.', 422);
        }
        return (int) $numero;
    }

    private static function error(Throwable $error) {
        if ($error instanceof ChatError) return [$error->getMessage(), $error->getCode()];
        Avisos::registrarError($error, 'usar el chat');
        return ['No se pudo conectar con el chat. Inténtalo de nuevo en unos momentos.', 500];
    }

    public function mensajes() {
        try {
            if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
                header('Allow: GET');
                throw new ChatError('Esta consulta requiere GET.', 405);
            }
            $usuario = self::usuario();
            $id = self::entero($_GET, 'id', 1);
            $antes = self::entero($_GET, 'antes');
            $despues = self::entero($_GET, 'despues');
            if ($antes > 0 && $despues > 0) throw new ChatError('Usa solo un cursor del chat.', 422);
            // El sondeo no retiene el bloqueo de sesión de PHP ni impide otros formularios.
            session_write_close();
            self::json(['ok' => true] + (new ChatM())->leer($id, $usuario, $antes, $despues));
        } catch (Throwable $error) {
            [$texto, $estado] = self::error($error);
            self::json(['ok' => false, 'error' => $texto], $estado);
        }
    }

    /** JSON para fetch y formulario convencional cuando JavaScript está desactivado. */
    public function enviar() {
        $json = strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;
        $id = 0;
        try {
            if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
                header('Allow: POST');
                throw new ChatError('Envía el mensaje desde el formulario del chat.', 405);
            }
            $usuario = self::usuario();
            $id = self::entero($_POST, 'id_siniestro', 1);
            $token = $_POST['csrf_token'] ?? null;
            if (!is_string($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
                throw new ChatError('El formulario venció. Recarga la página antes de enviar.', 403);
            }
            if ($json) session_write_close();
            $idMensaje = (new ChatM())->enviar($id, $usuario, $_POST['mensaje'] ?? null);
            if ($json) self::json(['ok' => true, 'id' => $idMensaje], 201);
            unset($_SESSION['chat_borrador']);
            Avisos::poner('success', 'Mensaje enviado.');
            header('Location: index.php?page=detalle&id=' . $id . '#chat', true, 303);
            exit;
        } catch (Throwable $error) {
            [$texto, $estado] = self::error($error);
            if ($json) self::json(['ok' => false, 'error' => $texto], $estado);
            if ($estado === 401) Avisos::ir('logIn', 'error', $texto);
            if ($estado === 403 || $id <= 0) Avisos::ir('misSiniestros', 'error', $texto);
            $valor = $_POST['mensaje'] ?? '';
            $_SESSION['chat_borrador'] = ['id' => $id, 'mensaje' => is_string($valor) ? substr($valor, 0, 8000) : ''];
            Avisos::poner('error', $texto);
            header('Location: index.php?page=detalle&id=' . $id . '#chat', true, 303);
            exit;
        }
    }
}
