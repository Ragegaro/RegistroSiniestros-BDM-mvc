<?php
require "config.php";
$page ="index";
 
if (isset($_GET['page']))
    $page=$_GET['page'];

switch($page){
    case 'login':
        require "controllers/UsuarioController.php";
        UsuarioController::index();
    break;  
    
   case 'loginAuth': 
        require "controllers/UsuarioController.php";
        UsuarioController::login();
    break;

    case 'logout': 
        require "controllers/UsuarioController.php";
        UsuarioController::logout();
    break;  

    case 'admin': 
        
        require "views/front/vLogout.php";

    break;




    
    default:echo "<a href ='".urlsite."?page=login'>LOGIN </a>";
    
    ; break;
};
