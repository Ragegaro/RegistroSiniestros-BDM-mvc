<?php require "views/layouts/header.php";
      require "views/layouts/navbar.php";
?>

<p class="titulo">¿Qué siniestro deseas reportar?</p>

<div class="grid-reportes">

    

    <div class=""><button onclick="location.href='<?php echo urlsite?>?page=choque'" >Choque</button></div>
    <div class=""><button disabled="disabled">Grúa</button></div>
    <div class=""><button disabled="disabled" >Combustible</button></div>
    <div class=""><button disabled="disabled">Paso de corriente</button></div>
    <div class=""><button disabled="disabled">Robo</button></div>
    <div class=""><button disabled="disabled">Cristales</button></div>
    <!--
    <div class="opc-sin"><a href="">Choque</a></div>
    <div class="opc-sin"><a href="">Grua</a></div>
    <div class="opc-sin"><a href="">Combustible</a></div>
    <div class="opc-sin"><a href="">Paso de corriente</a></div>
    <div class="opc-sin"><a href="">Robo</a></div>
    <div class="opc-sin"><a href="">Cristales</a></div>
    -->

</div>

<a class="llamar" href="">Llamar al Centro de Atención</a>

