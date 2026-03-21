<?php 
$alias= "admine";
?>
<div class="">

    <div class="texto">
        <h2>Bienvenido <?php echo $alias;?></h2>
        BUSQUEDA <br>     
       <?php require "views/layouts/busqueda.html";?>
        
    </div>
       <?php require "views/siniestros/listarSiniestrosV.php";?>
     
</div>
