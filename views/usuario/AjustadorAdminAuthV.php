<?php 
    require "controllers/loginC.php";
?>
<div class="">

    <div class="texto">
        <h2>Bienvenido <?php echo $_SESSION['alias']?></h2>
        BUSQUEDA <br>     
       <?php require "views/layouts/busqueda.html";?>
        
    </div>
       <?php require "views/siniestros/listarSiniestrosV.php";?>
     
</div>
