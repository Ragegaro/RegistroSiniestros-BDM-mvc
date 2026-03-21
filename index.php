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
        require "views/auth/RegisterV.php";
    break;

    //-----------LOG AUTH------------//
   case 'supervisor': 
      
        require "views/layouts/header.php";
        require "views/layouts/navbar.php";
        require "views/usuario/AjustadorAdminAuthV.php";
   break;

    case 'asegurado':
        require "views/layouts/header.php";
        require "views/layouts/navbar.php";
        require "views/usuario/UserAuthV.php";
    break;
    //-----------SINIESTROS--------------//

    case 'lista':
        require "views/layouts/header.php";
         require "views/layouts/navbar.php";
        require "views/siniestros/listarSiniestrosV.php";
    break;































/*
    case 'report':
        require "views/siniestros/tiposSiniestroV.php";
        
    break;
    
    case 'polizas':
        require "views/polizas/polizas.php";
        
    break;
        
    

    case 'choque': 
        require "views/siniestros/reporteV.php";
    break;

 

    
    
    case 'detalle':
        require "views/layouts/header.php";
        require "views/siniestros/detalle.php";
    break;

    case 'ajustes':
         require "views/usuario/EditProfileV.php";
    break;
*/













    

    default:
        require "controllers/LoginC.php";
        loginController::index();      
    ; break;
};
