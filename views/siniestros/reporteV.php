<h2>Reporte de Choque</h2>

<form action="<?php echo urlsite?>?page=guardarSiniestro" method="POST"  enctype="multipart/form-data" class="form-reporte" >

    <label>Número de póliza</label>
        
        <select name="SelectPoliza" id="poliza" required>
            <option value="">Seleccione una póliza</option>
                
            <?php
                foreach($misPolizas as $p): 
            ?>

            <option value="<?php echo $p->id;?>"> <?php echo $p->Poliza . ' ' . $p->Marca . ' ' . $p->Modelo/*. ' ' . $p->Aseguradora*/;?> </option>
            
            <?php endforeach;?>

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
    <label for=""> Foto del incidente</label>
    <input type="file" name="foto">
<br>
    <label>Descripción del suceso</label> <br>
    <textarea name="descripcion" rows="4" required></textarea>
<br>
    <!--<a class="vermas" href="<?php echo urlsite?>?page=detalle">Enviar Reporte</a>-->
    <button type="submit">Enviar reporte</button>

</form>