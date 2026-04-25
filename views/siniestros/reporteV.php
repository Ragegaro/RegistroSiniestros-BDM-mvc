<?php
    require "views/layouts/header.php";
    require "views/layouts/navbar.php";
    
    //  foreach($siniestros as $siniestro) { 
?>


<h2>Reporte de Choque<?php// echo $tipo; ?></h2>

<form action="guardar_reporte.php" method="POST" class="form-reporte">

<!--    <input type="hidden" name="tipo" value="<?php echo $tipo; ?>">-->

    <label>Número de póliza</label>
    <select name="poliza" id="poliza">
        <option value=""> Seleccione una póliza</option>
        <?php //foreach($polizas as $p):?>
        <option value="<?php echo $p['id_poliza'];?>">
            <?php// echo $P['NUMERO_POLIZA'].'-'.$p['coche']:?>
        </option>
        <?php ///endforeach;?>
    </select>
    
<br>

    <label>Dirección del incidente</label>
    <input type="text" name="ubicacion" required>

<br>

    <label>Fecha</label>
    <input type="date" name="fecha" required>

<br>

    <label>Hora</label>
    <input type="time" name="hora" required>
<br>

    <label>Descripción del suceso</label> <br>
    <textarea name="descripcion" rows="4" required></textarea>
<br>
<a class="vermas" href="<?php echo urlsite?>?page=detalle">Enviar Reporte</a>

<!--    <button type="submit">Enviar reporte</button>-->

</form