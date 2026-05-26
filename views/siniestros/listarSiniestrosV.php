<?php
    /*require "views/layouts/header.php";
    require "views/layouts/navbar.php";  
    require "controllers/LoginC.php";
    require_once "controllers/SiniestroC.php";
    $listaSiniestros = SiniestroC::listarSiniestros();*/
 
?>

<h1>Mis Siniestros</h1>

<div>

    <?php if (empty($listaSiniestros)): ?>
        <p>Aún no tiene siniestros.</p>
    <?php else:
        
        foreach($listaSiniestros as $siniestro): 
    ?>

        <div>
            <div>
                <p>
                    <strong>Póliza:</strong> <?php echo $siniestro->Poliza; ?><br>
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

