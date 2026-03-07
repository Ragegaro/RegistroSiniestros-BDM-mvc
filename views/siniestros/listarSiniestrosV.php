<?php
     require "views/layouts/header.php";
      require "views/layouts/navbar.php";
    
      //  foreach($siniestros as $siniestro) { 
    ?>

    <div class="contenedor-siniestros">
        <div class="siniestro">

            <div class="info">
                <p>
                    Póliza: <?php// echo $siniestro['poliza']; ?><br>
                    Modelo: <?php// echo $siniestro['modelo']; ?><br>
                    No. Siniestro: <?php// echo $siniestro['numero']; ?><br>
                    Fecha: <?//php echo $siniestro['fecha']; ?>
                </p>
            </div>

            <div class="acciones">
                <div class="estatus">
                    <p>Estatus:</p>
                    <?php //echo $siniestro['estatus']; ?>
                </div>

                <a class="vermas" href="?page=detalle&id=<?php echo $siniestro['id']; ?>">
                    Ver más
                </a>
        </div>

    </div>

<?php //} ?>


