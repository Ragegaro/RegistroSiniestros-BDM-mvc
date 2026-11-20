<?php
require "config.php";
require_once __DIR__ . '/lib/Seguridad.php';
require_once __DIR__ . '/lib/Avisos.php';
if (session_status() === PHP_SESSION_NONE) session_start();
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ob_start();
set_exception_handler(['Avisos', 'excepcionNoControlada']);
$page = isset($_GET['page']) && is_string($_GET['page']) ? $_GET['page'] : 'logIn';
Avisos::limpiarUrlAntigua($page);

switch ($page) {
    case 'logIn':
    case 'login':
        require "controllers/usuario/UsuarioC.php";
        (new UsuarioC())->index();
        break;
    case 'logInAuth':
        require "controllers/usuario/UsuarioC.php";
        (new UsuarioC())->logIn();
        break;
    case 'logout':
        require "controllers/usuario/UsuarioC.php";
        UsuarioC::logOut();
        break;
    case 'sigIn':
        require_once "controllers/usuario/UsuarioC.php";
        (new UsuarioC())->mostrarRegistro();
        break;
    case 'sigInAuth':
        require_once "controllers/usuario/UsuarioC.php";
        (new UsuarioC())->procesarRegistro();
        break;

    case 'supervisor':
    case 'admin':
    case 'ajustador':
    case 'asegurado':
        require_once "controllers/usuario/UsuarioC.php";
        (new UsuarioC())->mostrarPerfil();
        break;
    case 'miPerfil':
        require_once "controllers/usuario/UsuarioC.php";
        (new UsuarioC())->EditarPerfil();
        break;
    case 'editarPerfil':
        require_once "controllers/usuario/UsuarioC.php";
        (new UsuarioC())->procesarEdicion();
        break;

    case 'report':
        require_once "controllers/siniestro/SiniestroC.php";
        (new SiniestroC())->mostrarFormulario();
        break;
    case 'guardarSiniestro':
        require_once "controllers/siniestro/SiniestroC.php";
        (new SiniestroC())->agregarSiniestro();
        break;
    case 'misSiniestros':
        require_once "controllers/siniestro/SiniestroC.php";
        (new SiniestroC())->listarSiniestros();
        break;
    case 'detalle':
        require_once "controllers/siniestro/SiniestroC.php";
        (new SiniestroC())->verDetalledSiniestro();
        break;
    case 'guardarEvaluacion':
    case 'guardarEdicionEvaluacion':
        require_once "controllers/siniestro/SiniestroC.php";
        (new SiniestroC())->guardarEvaluacion();
        break;
    case 'actualizarEstatus':
        require_once "controllers/siniestro/SiniestroC.php";
        (new SiniestroC())->actualizarEstatus();
        break;

    case 'asignaciones':
        require_once 'controllers/siniestro/SiniestroC.php';
        (new SiniestroC())->mostrarAsignaciones();
        break;
    case 'asignarAjustador':
        require_once 'controllers/siniestro/SiniestroC.php';
        (new SiniestroC())->asignarAjustador();
        break;
    case 'evidencia':
        require_once 'controllers/siniestro/MultimediaC.php';
        (new MultimediaC())->ver();
        break;
    case 'chatMensajes':
        require_once 'controllers/siniestro/ChatC.php';
        (new ChatC())->mensajes();
        break;
    case 'chatEnviar':
        require_once 'controllers/siniestro/ChatC.php';
        (new ChatC())->enviar();
        break;

    case 'polizas':
        require "controllers/poliza/PolizaC.php";
        (new PolizaC())->listarPolizas();
        break;
    case 'agregarPoliza':
        require_once 'controllers/poliza/PolizaC.php';
        (new PolizaC())->mostrarFormularioAgregar();
        break;
    case 'guardarPoliza':
        require_once 'controllers/poliza/PolizaC.php';
        (new PolizaC())->guardarPoliza();
        break;

    case 'guardarAuto':
        require_once "controllers/poliza/AutoC.php";
        (new AutoC())->agregarAuto();
        break;
    case 'agregarAuto':
        require "views/layouts/header.php";
        require "views/polizas/autos/agregarAutoV.php";
        require "views/layouts/footer.php";
        break;

    default:
        require "controllers/usuario/UsuarioC.php";
        (new UsuarioC())->index();
        break;
}
