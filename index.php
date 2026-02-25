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

    case 'admin': 
        require "views/front/LogoutV.php";
    break;
    
    default:
        require "controllers/LoginC.php";
        loginController::index();
    //echo "<a href ='".urlsite."?page=login'>LOGIN </a>";
      
    ; break;
};
