<?php
/** Pruebas sin DB. Ejecutar: C:\xampp\php\php.exe pruebas\reglas.php */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once __DIR__ . '/../lib/Seguridad.php';
require_once __DIR__ . '/../lib/Archivos.php';

$cuenta = 0;
function comprobar($condicion, $descripcion) {
    global $cuenta;
    if (!$condicion) throw new RuntimeException('FALLO: ' . $descripcion);
    $cuenta++;
    echo 'OK: ' . $descripcion . PHP_EOL;
}

$caso = ['Usuario' => 10, 'ajustador_id' => 20];
comprobar(Seguridad::puedeVer($caso, 1, 'supervisor'), 'Supervisor ve el expediente');
comprobar(Seguridad::puedeVer($caso, 1, 'admin'), 'Admin conserva acceso de supervisor');
comprobar(Seguridad::puedeVer($caso, 10, 'asegurado'), 'Propietario ve su expediente');
comprobar(!Seguridad::puedeVer($caso, 11, 'asegurado'), 'Otro asegurado no ve el expediente');
comprobar(Seguridad::puedeVer($caso, 20, 'ajustador'), 'Ajustador asignado ve el expediente');
comprobar(!Seguridad::puedeVer($caso, 21, 'ajustador'), 'Otro ajustador no ve el expediente');
$caso['ajustador_id'] = 21;
comprobar(!Seguridad::puedeVer($caso, 20, 'ajustador'), 'Reasignar retira acceso al anterior');
comprobar(Seguridad::puedeVer($caso, 21, 'ajustador'), 'Reasignar da acceso al nuevo responsable');
comprobar(!Seguridad::puedeVer($caso, 0, 'supervisor'), 'Sin usuario no hay acceso');
comprobar(!Seguridad::puedeVer($caso, 10, 'desconocido'), 'Rol desconocido no tiene acceso');
comprobar(!Seguridad::puedeVer(false, 1, 'supervisor'), 'Un expediente inexistente no es accesible');
comprobar(Archivos::rango('', 100) === [0, 99, 200], 'Solicitud completa');
comprobar(Archivos::rango('bytes=0-9', 100) === [0, 9, 206], 'Primeros bytes');
comprobar(Archivos::rango('bytes=10-', 100) === [10, 99, 206], 'Rango abierto');
comprobar(Archivos::rango('bytes=-10', 100) === [90, 99, 206], 'Últimos bytes');
comprobar(Archivos::rango('bytes=0-999', 100) === [0, 99, 206], 'Final se ajusta al tamaño real');
comprobar(Archivos::rango('bytes=-999', 100) === [0, 99, 206], 'Sufijo mayor que el archivo');
foreach (['bytes=100-', 'bytes=10-5', 'bytes=-0', 'bytes=-', 'bytes=0-1,5-6', 'invalid'] as $rango) {
    $rechazado = false;
    try { Archivos::rango($rango, 100); } catch (InvalidArgumentException $e) { $rechazado = true; }
    comprobar($rechazado, 'Rechazar rango ' . $rango);
}
comprobar(Archivos::rutaHeredada('https://example.com/video.mp4') === false, 'Rechazar URLs externas');
comprobar(Archivos::rutaHeredada('uploads/siniestros/../../config.php') === false, 'Rechazar lectura fuera de evidencias');
comprobar(Archivos::rutaHeredada('C:\\xampp\\php\\php.ini') === false, 'Rechazar rutas absolutas');
comprobar(Archivos::validarCarga(null) === [], 'Permitir reporte sin evidencias');
$_SESSION = [];
$token = Seguridad::token();
comprobar(strlen($token) === 64 && $token === Seguridad::token(), 'Token CSRF estable durante la sesión');
comprobar(Seguridad::escapar('<script>') === '&lt;script&gt;', 'Escapar contenido HTML');
echo PHP_EOL . $cuenta . ' comprobaciones completadas.' . PHP_EOL;
