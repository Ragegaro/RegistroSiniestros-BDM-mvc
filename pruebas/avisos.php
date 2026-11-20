<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../lib/Seguridad.php';

$numero = 0;
function verificarAviso($condicion, $nombre) {
    global $numero;
    if (!$condicion) throw new RuntimeException('FALLO: ' . $nombre);
    $numero++;
    echo 'OK: ' . $nombre . PHP_EOL;
}

$_SESSION = [];
Avisos::poner('error', 'Alias o contraseña incorrectos.');
$avisos = Avisos::extraer();
verificarAviso(count($avisos) === 1 && $avisos[0]['tipo'] === 'error', 'El aviso se guarda en sesión');
verificarAviso(Avisos::extraer() === [], 'Se consume una sola vez');
Avisos::poner('clase-no-permitida', 'Mensaje');
verificarAviso(Avisos::extraer()[0]['tipo'] === 'info', 'Se restringen los tipos de aviso');
for ($i = 0; $i < 10; $i++) Avisos::poner('info', 'Aviso ' . $i);
$avisos = Avisos::extraer();
verificarAviso(count($avisos) === 5 && $avisos[0]['texto'] === 'Aviso 5', 'La cola conserva solo los últimos cinco avisos');

Avisos::poner('error', '<img src=x onerror=alert(1)>');
ob_start();
require __DIR__ . '/../views/layouts/avisos.php';
$html = ob_get_clean();
verificarAviso(strpos($html, '<img') === false && strpos($html, '&lt;img') !== false, 'La vista escapa HTML del mensaje');
verificarAviso(strpos($html, 'role="alert"') !== false, 'Error accesible para lectores de pantalla');

$error = new PDOException('SQL y datos privados que no deben mostrarse');
$error->errorInfo = ['HY000', 1153, 'Texto privado del servidor'];
$texto = Avisos::mensajeError($error, 'guardar el video');
verificarAviso(strpos($texto, 'max_allowed_packet') !== false, 'El código de paquete grande tiene explicación específica');
verificarAviso(strpos($texto, 'privad') === false && strpos($texto, '1153') !== false, 'Se muestra el código, no los datos privados');
$error->errorInfo = ['HY000', 9999, 'Consulta completa con contraseña'];
$texto = Avisos::mensajeError($error, 'guardar el video');
verificarAviso(strpos($texto, 'max_allowed_packet') === false, 'Un error desconocido no se atribuye al tamaño del paquete');
verificarAviso(strpos($texto, 'contraseña') === false && strpos($texto, '9999') !== false, 'El error desconocido también protege los datos');
$error->errorInfo = ['23000', 1062, 'Duplicate entry dato-privado'];
verificarAviso(strpos(Avisos::mensajeError($error), 'dato-privado') === false, 'Los duplicados no exponen valores del SQL');
echo PHP_EOL . $numero . ' comprobaciones completadas.' . PHP_EOL;
