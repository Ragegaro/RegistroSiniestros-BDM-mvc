<button disabled="disabled">CHAT </button>
<div class="contenedor">
    <div class="datos">
        <!--icon-->
        <h2>Datos del siniestro </h2><p>Tipo de siniestro : Choque</p>
    </div>
    <div class="info-siniestro">
        <p><strong>No. de siniestro: </strong> <?//id_siniestro?></p>
        <p><strong><?//estatus?>estatus</strong> </p>
        <p><strong>Ajustador: </strong><?//ajustador?>  </p>
        <p><strong>Ubicacion: </strong><?php//deireccion?></p>
    </div>
    <div class="datos-poliza">
        <button><strong>Datos de la poliza</strong><br>No. de Poliza: 12345678<?//idPoliza?></button>
    </div>
    <div class="seguimiento">
        <p><strong>Seguimiento</strong></p>
        
        <?php// foreach?>
            <div class="segui-estatus">
                <p><?php //estatus br fecha y hora del siniestro?>Registro de siniestro <br>Hora</p>
            </div>
        <?//endforeach?>
            
    </div>
</div>

<!--Aqui por medio del controlador se activa o desactiva lo siguiente-->

<?php require "views/siniestros/evaluacionV.php";?>