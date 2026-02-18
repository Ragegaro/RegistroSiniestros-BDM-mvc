<?php
session_start();
require "MODELO/ModeloLogIn.php";

class LogInController{

    public function index(){
        if (isset($_SESSION['login']))
            header('location:'.urlsite);
            require "VISTA/front/FormLogIn.php";
    }

    public function login(){
        $_modelo = new LogIn();
        $_email = trim($_POST['txtemail']);
        $_pass = /* falta encriptado*/ trim($_POST['txtpassword']);

        $_resultado = $_modelo->LogIn($_email,$_pass);
        if ($_resultado){
            $_SESSION['login']=$email;
            header('location:'.urlsite."?page=admin");
        }
        else{
            header('location:'.urlsite."?msg=No coinciden las credenciales");
        }
    }
}