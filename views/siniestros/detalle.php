<?php 
    require "views/layouts/header.php";
    require "views/layouts/navbar.php";
    require_once "controllers/SiniestroC.php";


    $siniestroC = new SiniestroC();
    $siniestro = $siniestroC->verDetalledSiniestro();

    if(!$siniestro) {
        echo "<div class='contenedor'><h2>Siniestro no encontrado.</h2></div>";
        exit;
    }
?>

<button disabled="disabled">CHAT </button>
<div class="contenedor">
    <!--<div class="datos">
        icon
        <h2>Datos del siniestro </h2><p>Tipo de siniestro : Choque</p>
    </div>-->
   
    <div class="info-siniestro">
        <h2>Datos del siniestro</h2><br>
        <strong>No. de siniestro: </strong> <?php echo $siniestro['id']; ?> <br>
        <strong>No. de Poliza:</strong> <?php echo $siniestro['Poliza']; ?> <strong> Aseguradora: </strong> <?php echo $siniestro['Aseguradora']; ?>   <br>  
        <strong>Auto: </strong> <?php echo $siniestro['Marca']; ?>  <strong> Modelo: </strong> <?php echo $siniestro['Modelo']; ?></strong><br>
        <strong>Ajustador: </strong> <?php echo $siniestro['Ajustador']; ?><br>
        <strong>Ubicacion: </strong> <?php echo $siniestro['direccion']; ?><br>
        <strong>Fecha:   </strong> <?php echo $siniestro['fecha']; ?>  <strong> Hora: </strong> <?php echo $siniestro['hora']; ?></strong><br>
        <strong>Estatus:</strong> <?php echo $siniestro['Estatus']; ?><br>
        <br>
        <p><strong>Descripción: </strong> <?php echo $siniestro['descripcion']; ?> </p>

    </div>
    
    <div class="seguimiento">
        <h2><strong>Seguimiento</strong></h2>
        
        <?php// foreach?>
            <div class="segui-estatus">
                <p><?php //estatus br fecha y hora del siniestro?>Registro de siniestro <br>Hora</p>
            </div>
        <?//endforeach?>
            
    </div>
</div>

<!--Aqui por medio del controlador se activa o desactiva lo siguiente-->

<?php 
require "views/siniestros/evaluacionV.php";/*
    if(isset($_SESSION['rol'])&& $_SESSION['rol_id']=== '2'){
        require "views/siniestros/evaluacionV.php";
    }
        if(isset($_SESSION['rol'])&& $_SESSION['rol_id']==='1'){
        require "views/siniestros/evaluacionV.php";
    }*/
?>