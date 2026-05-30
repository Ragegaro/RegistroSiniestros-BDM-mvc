<h1>Siniestros</h1>

<div class="contenedor-multiple">

    <?php if (empty($misSiniestros)): ?>
        <p>Aún no tiene siniestros.</p>
    <?php else:
        
        foreach($misSiniestros as $siniestro): 
    ?>

        <div class="P-S-individual">
            <div class="info">
                <p>
                    <strong>Póliza:</strong> <?php echo $siniestro->Poliza; ?><br>
                    <strong>Modelo:</strong> <?php echo $siniestro->Modelo; ?><br>
                    <strong>No. Siniestro:</strong> <?php echo $siniestro->id; ?><br>
                    <strong>Fecha:</strong> <?php echo $siniestro->fecha; ?>
                </p>
            </div>

            <div class="acciones">
                <div class="estatus">
                    <p><strong>Estatus:</strong></p>
                    <?php echo $siniestro->Estatus; ?>
                </div>

                <a class="vermas" href="<?php echo urlsite?>?page=detalle&id=<?php echo $siniestro-> id;?>">
                  Ver más
                </a>
            </div>
        </div>

    <?php  endforeach;
        endif;
    ?>


</div>



<?php //
//  end foreach} ?>

