<?php
/** Pruebas de permisos, validación y salida; no conectan ni escriben en MySQL. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../lib/ChatReglas.php';
$total = 0;
function verificarChat($condicion, $nombre) {
    global $total;
    if (!$condicion) throw new RuntimeException('FALLO: ' . $nombre);
    $total++;
    echo 'OK: ' . $nombre . PHP_EOL;
}
function accesoChat($caso, $actor) {
    try { ChatReglas::autorizar($caso, $actor); return true; }
    catch (ChatError $e) { return false; }
}
function rechazaChat($valor) {
    try { ChatReglas::mensaje($valor); return false; }
    catch (ChatError $e) { return $e->getCode() === 422; }
}
$caso = ['Usuario' => 10, 'ajustador_id' => 20];
$actor = function ($id, $rol, $estado = 1) { return ['id' => $id, 'rol' => $rol, 'estatus' => $estado]; };
verificarChat(accesoChat($caso, $actor(10, 'asegurado')), 'Propietario puede leer y enviar');
verificarChat(!accesoChat($caso, $actor(11, 'asegurado')), 'Otro asegurado no participa');
verificarChat(accesoChat($caso, $actor(20, 'ajustador')), 'Ajustador asignado participa');
verificarChat(!accesoChat($caso, $actor(21, 'ajustador')), 'Ajustador ajeno no participa');
verificarChat(accesoChat($caso, $actor(1, 'supervisor')), 'Supervisor accede');
verificarChat(accesoChat($caso, $actor(2, 'admin')), 'Admin equivale a supervisor');
verificarChat(accesoChat(['Usuario' => 99, 'ajustador_id' => null], $actor(1, 'supervisor')), 'Supervisor accede a otro caso sin asignación');
$caso['ajustador_id'] = 21;
verificarChat(!accesoChat($caso, $actor(20, 'ajustador')), 'Reasignar revoca al anterior');
verificarChat(accesoChat($caso, $actor(21, 'ajustador')), 'Nuevo ajustador puede participar');
$caso['ajustador_id'] = null;
verificarChat(!accesoChat($caso, $actor(21, 'ajustador')), 'Retirar responsable revoca su acceso');
verificarChat(accesoChat($caso, $actor(10, 'asegurado')), 'Asegurado conserva chat sin ajustador');
verificarChat(!accesoChat($caso, $actor(1, 'supervisor', 0)), 'Cuenta inactiva pierde acceso incluso si es supervisora');
verificarChat(!accesoChat($caso, $actor(0, 'supervisor')), 'No se permite usuario cero');
verificarChat(!accesoChat(false, $actor(1, 'supervisor')), 'No se permite caso inexistente');
verificarChat(!accesoChat($caso, null), 'No se permite actor inexistente');
$_SESSION = ['id_usuario' => 11, 'rol_slug' => 'supervisor'];
verificarChat(!accesoChat($caso, $actor(11, 'asegurado')), 'Un rol antiguo de sesión no sustituye al rol actual de SQL');
verificarChat(ChatReglas::mensaje("  Hola\r\nEquipo  ") === "Hola\nEquipo", 'Normaliza bordes y saltos de línea');
verificarChat(ChatReglas::mensaje('¿Todo bien? 🚗') === '¿Todo bien? 🚗', 'Admite UTF-8 y emoji');
verificarChat(ChatReglas::mensaje(str_repeat('á', 2000)) === str_repeat('á', 2000), 'Límite de 2000 caracteres, no bytes');
verificarChat(ChatReglas::mensaje(str_repeat('🚗', 2000)) === str_repeat('🚗', 2000), 'Admite 2000 puntos Unicode de cuatro bytes');
foreach (['', " \t\n", "\u{00A0}", [], null, 123, "a\0b", "a\x01b", "\xFF", str_repeat('a', 2001), str_repeat('🚗', 2001)] as $valor) {
    verificarChat(rechazaChat($valor), 'Rechaza mensaje vacío, inválido o excesivo');
}
$malicioso = '<script>alert("hola")</script>';
verificarChat(ChatReglas::mensaje($malicioso) === $malicioso, 'Se conserva texto literal sin interpretar HTML');
verificarChat(ChatReglas::nombreRol('admin') === 'Supervisor', 'Etiqueta del administrador');
verificarChat(ChatReglas::nombreRol('otro') === 'Participante', 'Etiqueta segura por defecto');
$_SESSION = ['id_usuario' => 10];
$mensaje = ['id' => 5, 'usuario_id' => 10, 'autor' => '<img src=x onerror=alert(1)>',
    'rol_nombre' => 'Asegurado', 'mensaje' => $malicioso, 'fecha' => '2026-01-01 12:00:00'];
ob_start();
require __DIR__ . '/../views/siniestros/chat/mensaje.php';
$html = ob_get_clean();
verificarChat(strpos($html, '<script>') === false && strpos($html, '&lt;script&gt;') !== false, 'Vista escapa mensaje');
verificarChat(strpos($html, '<img') === false && strpos($html, '&lt;img') !== false, 'Vista escapa autor');
verificarChat(strpos($html, 'is-mine') !== false && strpos($html, 'data-id="5"') !== false, 'Vista identifica mensaje propio');
echo PHP_EOL . $total . ' comprobaciones del chat completadas.' . PHP_EOL;
