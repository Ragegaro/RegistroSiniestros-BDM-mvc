<h1>Siniestros</h1>

<div>

    <?php if (empty($misSiniestros)): ?>
        <p>Aún no tiene siniestros.</p>
    <?php else:
        
        foreach($misSiniestros as $siniestro): 
    ?>

        <div>
            <div>
                <p>
                    <strong>Póliza:</strong> <?php echo $siniestro->Poliza; ?><br>
                    <?php if ($_SESSION['rol_slug'] !== 'asegurado'): ?>
                        <strong>Nombre del Asegurado:</strong> <?php echo $siniestro->nombreCliente; ?><br>
                    <?php endif; ?>
                    <strong>Modelo:</strong> <?php echo $siniestro->Modelo; ?><br>
                    <strong>No. Siniestro:</strong> <?php echo $siniestro->id; ?><br>
                    <strong>Fecha:</strong> <?php echo $siniestro->fecha; ?>
                    
                </p>
            </div>

            <div>
                <div>
                    <p><strong>Estatus:</strong></p>
                    <?php echo $siniestro->Estatus; ?>
                </div>

                <a href="<?php echo urlsite?>?page=detalle&id=<?php echo $siniestro-> id;?>">
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

