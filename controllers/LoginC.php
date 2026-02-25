<?php
session_start();
require "models/LoginM.php";

class loginController{
    public static function index(){
        if (isset($_SESSION['login']))
            header('location:'.urlsite);
        require "views/front/LoginV.php";
                
    }

    public static function login(){
        
        $_modelo = new LoginM();
        $_alias = trim($_POST ['txtalias']);
        $_pass = md5(trim($_POST['txtpassword']));
        $_resultado = $_modelo->logIn($_alias,$_pass);

        if ($_resultado){
            $_SESSION['login'] = $_alias;
            header('location:'.urlsite."?page=admin");
            
        }
        else {
            header('location:'.urlsite."?msg=No coinciden las credenciales");
            
        }
        
    }   

    public static function logout(){
       if (!isset($_SESSION['login']))
            header('location:'.urlsite); 
        unset ($_SESSION['login']);
        session_destroy();
        header('location:'.urlsite); 
    }
}