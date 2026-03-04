<?php
require "config.php";
$page ="index";
 
if (isset($_GET['page']))
    $page=$_GET['page'];

switch($page){
    case 'login':
        require "controllers/LoginC.php";
        loginController::index();
    break;  
    
    case 'loginAuth': 
        require "controllers/LoginC.php";
        loginController::login();
    break;

    case 'logout': 
        require "controllers/LoginC.php";
        loginController::logout();
    break;  


    case 'report':
        require "views/front/asegurado/reportSiniestro.php";
        require "views/front/LogoutV.php";
        break;
    
    case 'polizas':
        require "views/front/asegurado/polizas.php";
        require "views/front/LogoutV.php";
        break;
        
    case 'siniestros':
        require "views/front/asegurado/siniestros.php";
        //require "views/front/LogoutV.php";
        break;



    case 'supervisor': 
        require "views/front/asegurado/UserAuthV.php";
        //require "views/front/LogoutV.php";
    break;
   

    default:
        require "controllers/LoginC.php";
        loginController::index();
    //echo "<a href ='".urlsite."?page=login'>LOGIN </a>";
      
    ; break;
};
