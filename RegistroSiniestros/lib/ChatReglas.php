<?php
require_once __DIR__ . '/Seguridad.php';

/** Errores controlados del chat; el código se utiliza como estado HTTP. */
class ChatError extends RuntimeException {}

final class ChatReglas {
    const MAX_CARACTERES = 2000;
    const POR_PAGINA = 50;

    /** No depende de mbstring. Conserva saltos de línea, nunca interpreta HTML. */
    public static function mensaje($valor) {
        if (!is_string($valor) || strlen($valor) > self::MAX_CARACTERES * 4 || !preg_match('//u', $valor)) {
            throw new ChatError('Escribe un mensaje válido de hasta 2000 caracteres.', 422);
        }
        $texto = str_replace(["\r\n", "\r"], "\n", $valor);
        $texto = preg_replace('/^\s+|\s+$/u', '', $texto);
        if ($texto === '' || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $texto)) {
            throw new ChatError('El mensaje está vacío o contiene caracteres no permitidos.', 422);
        }
        if (preg_match_all('/./us', $texto) > self::MAX_CARACTERES) {
            throw new ChatError('El mensaje no puede superar los 2000 caracteres.', 422);
        }
        return $texto;
    }

    /** El actor proviene de usuario + rol en SQL, no de campos del navegador. */
    public static function autorizar($caso, $actor) {
        if (!$actor || (int) ($actor['estatus'] ?? 0) !== 1) {
            throw new ChatError('Tu cuenta ya no está activa. Vuelve a iniciar sesión.', 401);
        }
        if (!Seguridad::puedeVer($caso, (int) $actor['id'], $actor['rol'])) {
            // Misma respuesta para un ID ajeno o inexistente: no revela otros casos.
            throw new ChatError('No tienes acceso a este chat o el siniestro ya no está disponible.', 403);
        }
    }

    public static function nombreRol($rol) {
        $nombres = ['asegurado' => 'Asegurado', 'ajustador' => 'Ajustador',
            'supervisor' => 'Supervisor', 'admin' => 'Supervisor'];
        return $nombres[$rol] ?? 'Participante';
    }
}
