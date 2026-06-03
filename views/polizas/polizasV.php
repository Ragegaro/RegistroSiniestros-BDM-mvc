<div class="polizas">
    <h1>Mis polizas</h1>
    <!--<button  disabled="disabled"> Agregar polizas</button>-->
</div>   

<div class="contenedor-multiple">
    <?php if (empty($misPolizas)): ?>
    
        <p>Aún no tienes pólizas registradas.</p>
    
    <?php else:
        foreach($misPolizas as $poliza): 
    ?>

    <div class="P-S-individual">
        <div class="info">
            <strong>No. de Póliza:</strong> <?php echo $poliza->Poliza; ?> <br>    
            <strong>Marca:</strong> <?php echo $poliza->Marca; ?> <br> 
            <strong>Modelo:</strong> <?php echo $poliza->Modelo; ?><br>
            <strong>Aseguradora:</strong> <?php echo $poliza->Aseguradora; ?> <br>
           <!-- <strong>Fecha de Inicio:</strong> <?php //echo $poliza->FechaInicio; ?> <br>-->
        </div>

        <div class="acciones">
            <div class="estatus">
                <button class="btn"> Eliminar</button>
            </div>
            <a class="vermas" href="<?php echo urlsite?>?page=detallePoliza">
                Ver más
            </a>
        </div>

    </div>
        
        <?php endforeach; 
        endif; ?>                



</div>   