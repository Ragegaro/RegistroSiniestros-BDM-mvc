<?php
require "config.php";
$page ="index";
 
if (isset($_GET['page']))
    $page=$_GET['page'];

switch($page){
    //-----------LOGIN--------------//
    case 'login':
        require "controllers/LoginC.php";
        loginController::index();
    break;  
    
    case 'logInAuth': 
        require "controllers/LoginC.php";
        loginController::login();
    break;

    case 'logout': 
        require "controllers/LoginC.php";
        loginController::logout();
    break;

    case 'sigIn':
        require_once "controllers/UsuarioC.php";
        $usuario = new UsuarioC();
        $usuario->mostrarRegistro(); 
    break;

    case 'sigInAuth':
        require_once "controllers/UsuarioC.php";
        $usuario = new UsuarioC();
        $usuario->procesarRegistro(); 
    break;

    //-----------LOG AUTH------------//
   case 'supervisor': 
     //   require "views/usuario/AjustadorAdminAuthV.php";
   break;

    case 'asegurado':
       /* require "views/layouts/header.php";
        require "views/layouts/navbar.php";
        require "views/usuario/UserAuthV.php";*/

    break;

  case 'editarPerfil':
        require_once "controllers/UsuarioC.php";
        $usuario = new UsuarioC();
        $usuario->procesarEdicion(); 
    break;
    



//--BOTONES MNAVBAR --//


    case 'ajustes':
            //require "views/usuario/EditProfileV.php";
        break;


    case 'guardarAuto':
        require_once "controllers/AutoC.php"; 
        $auto = new AutoC();
        $auto->agregarAuto(); 
    break;




    case 'agregarAuto':
        require "views/layouts/header.php";
        require "views/autos/agregarAutoV.php";
    break;





//GUARDA SINIETROS

    case 'guardarSiniestro':
        require_once "controllers/SiniestroC.php";
        $siniestro = new SiniestroC();
        $siniestro->agregarSiniestro(); 
    break;


    case 'report':
        require "views/siniestros/reporteV.php";   
        require_once "controllers/PolizaC.php";     
    break;


    case 'lista':   
        require "views/siniestros/listarSiniestrosV.php";
    break;

    case 'guardarEvaluacion':
        require_once "controllers/SiniestroC.php";
        $siniestro = new SiniestroC();
        $siniestro->procesarEvaluacionYMultimedia();
    break;

    ///POLIZAs
    
    case 'polizas':
        require "views/polizas/polizasV.php";
        
    break;
        /*
    case 'choque': 
        require "views/siniestros/reporteV.php";
    break;    
    */
    case 'detalle':
        require "views/siniestros/detalle.php";
    break;
/*
    case 'ajustes':
         require "views/usuario/EditProfileV.php";
    break;
*/













    

    default:
        require "controllers/LoginC.php";
        loginController::index();      
    ; break;
};
