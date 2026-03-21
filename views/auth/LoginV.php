<?php //require "views/layouts/header.php";?>

<div class="bg-login">

    <div class="container">
        <div class="row justify-content-center">   
            <form action="<?php echo urlsite?>?page=logInAuth" method="post">
                <input type="text" class="form-control" name="txtalias" placeholder="Nombre de usuario">
                <input type="password" class="form-control" name="txtpassword" placeholder="Contraseña">
                <input type ="submit" class="btn btn-primary" value="Iniciar sesión" name="btnLogIn">
            </form>
            
            
                <!--esto podria ser con JS?-->
        <p class ="text-danger"><?php echo (isset($_GET['msg'])) ? $_GET['msg']:"" ?></p>
        </div>
         <a href="<?php echo urlsite ?>?page=sigIn">Registrarse</a>
       
    </div>
</div>


