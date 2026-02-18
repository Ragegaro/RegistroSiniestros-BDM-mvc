<?php 
    require "config.php";
    $page ="index";
    if (isset($_GET['page']))
        $page = $_GET['page'];

    switch ($page){
        case  'LogIn' :
            require "CONTROLADOR/LogInController.php";
            LogInController::index();
        break ;

        case  'logInAuth':
            require "CONTROLADOR/LogInController.php";
            LogInController::login();
        break;

        case  'LogOut' : break ;
        
        case  'admin' :
            echo "logueado";
        break ;
        
        default: echo "<a href='".urlsite."?page=login'> LOGIN </a> "; break;

    }
    


?>
