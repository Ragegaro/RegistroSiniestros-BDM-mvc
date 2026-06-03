<?php
    require "config.php";
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $page = isset($_GET['page']) ? $_GET['page'] :'logIn';

    switch($page){
    //-----------Usuarios------------//
    //           LOG IN              //
        case 'logIn':
            require "controllers/usuario/UsuarioC.php";
            $controller= new UsuarioC();
            $controller->index();
        break;  
        
        case 'logInAuth': 
            require "controllers/usuario/UsuarioC.php";
            $controller=new UsuarioC();
            $controller->logIn();
        break;


        case 'logout': 
            require "controllers/usuario/UsuarioC.php";
            $controller= new UsuarioC();
            $controller->logOut();
        break;
    //           SIG IN              //
        case 'sigIn':
            require_once "controllers/usuario/UsuarioC.php";
            $usuario = new UsuarioC();
            $usuario->mostrarRegistro(); 
        break;

        case 'sigInAuth':
            require_once "controllers/usuario/UsuarioC.php";
            $usuario = new UsuarioC();
            $usuario->procesarRegistro(); 
        break;

    //           PERFILES            //
    case 'supervisor': 
            require_once "controllers/usuario/UsuarioC.php";
            $controller = new UsuarioC();
            $controller->mostrarPerfil();
    break;

        case 'ajustador':
            require_once "controllers/usuario/UsuarioC.php";
            $controller = new UsuarioC();
            $controller->mostrarPerfil();
        break;

        case 'asegurado':
            require_once "controllers/usuario/UsuarioC.php";
            $controller = new UsuarioC();
            $controller->mostrarPerfil();
        break;

    


        case 'miPerfil':
            require_once "controllers/usuario/UsuarioC.php";
            $usuario = new UsuarioC();
            $usuario->EditarPerfil();   
            //    require "views/usuario/EditProfileV.php";
        break;

        case 'editarPerfil':
            require_once "controllers/usuario/UsuarioC.php";
            $usuario = new UsuarioC();
            $usuario->procesarEdicion(); 
        break;


        /*
            case 'agregarAdminAjust':
                require_once "controllers/usuario/UsuarioC.php";
                $usuario = new UsuarioC();
                $usuario->agregarAdminAjust();
            break;  
            
            case 'editarSupervisor':
                require_once "controllers/usuario/UsuarioC.php";
                $usuario = new UsuarioC();
                $usuario->editarSupervisor();
            break;

            case 'editarAjustador':
            require_once "controllers/usuario/UsuarioC.php";
            $usuario = new UsuarioC();
            $usuario->editarAjustador();

            case 'listar usuarios':
                require_once "controllers/usuario/UsuarioC.php";
                $usuario = new UsuarioC();
                $usuario->listarUsuarios();

        */

    //            ROLES              //
    //----------Siniestros-----------//
    //        Alta siniestro         //
        case 'report':
            require_once "controllers/siniestro/SiniestroC.php";
            $siniestro = new SiniestroC();
            $siniestro->mostrarFormulario(); 
        break;

        case 'guardarSiniestro':
            require_once "controllers/siniestro/SiniestroC.php";
            $siniestro = new SiniestroC();
            $siniestro->agregarSiniestro(); 
        break;

        case 'guardarEvaluacion':
            require_once "controllers/SiniestroC.php";
            $siniestro = new SiniestroC();
            $siniestro->procesarEvaluacionYMultimedia();
        break;
    //       Lectura siniestros      //
    
        case 'misSiniestros':   
            require_once "controllers/siniestro/SiniestroC.php";
            $controller = new SiniestroC();
            $controller->listarSiniestros();
        break;
    
        case 'detalle':
            require_once "controllers/siniestro/SiniestroC.php";
            $controller = new SiniestroC();
            $controller->verDetalledSiniestro();
        break;

    //-----------Polizas-------------//
    //        Lectura polizas        //
        
        case 'polizas':
            require "controllers/poliza/PolizaC.php";
            $controller = new PolizaC();
            $controller->listarPolizas();
            
        break;

        /*
            case 'detalle':
                require "views/siniestros/detalle.php";
            break;
        */

            
    //            Autos              //
    case 'guardarAuto':
            require_once "controllers/AutoC.php"; 
            $auto = new AutoC();
            $auto->agregarAuto(); 
        break;





        

    //--------BOTONES MNAVBAR--------//
    

        case 'agregarAuto':
            require "views/layouts/header.php";
            require "views/autos/agregarAutoV.php";
        break;

        
    //------------DEFAULT------------//
        default:
            require "controllers/usuario/UsuarioC.php";
            $controller= new UsuarioC();
            $controller->index();
        break;
};
