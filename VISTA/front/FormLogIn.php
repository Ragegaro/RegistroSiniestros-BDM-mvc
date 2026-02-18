<?php require "VISTA/layout/header.php";?>

<div class="container">

    <div class="row justify-content-center">

        <div class="col-sm-4 mt-5  mb-5">

            <form action="<?php echo urlsite?>?page=logInAuth" method="post">
                
                <div class="form-group">
                    <input type="email" class ="form-control" name="txtemail" placeholder="EMAIL">
                </div>             
                <div class="form-group">
                    <input type="password" class ="form-control" name="txtpassword" placeholder="CONTRASEÑA">
                </div>

                <input type ="submit" class ="btn btn-primary btn-block" value="Iniciar Sesion" name ="btnLogIn">

            </form>

            <p class ="text-danger"><?php echo (isset ($_GET['msg'])) ? $_GET['msg'] : "" ?> </p>

        </div>
    </div>

</div>

<?php require "VISTA/layout/footer.php";?>