<?php
require "views/layouts/header.php";
?>

<div class="polizas">
    <h1>Mis polizas</h1>
    <button  disabled="disabled"> Agregar polizas</button>
</div>

<div class="contenedor-multiple">

    <div class="P-S-individual">
        <div class="info">
            <p>Auto <br>   <?php?>
               No. de Póliza: <?php ?><br>
               Certificado <?php// echo $siniestro['modelo']; ?><br>
               Estatus:<?php// echo $siniestro['numero']; ?><br>           
            </p>
        </div>

        <div class="acciones">
            <div class="estatus">
                <button class="btn"> Eliminar</button>
            </div>
            <a class="vermas" href="?page=detalle&id=<?php echo $siniestro['id']; ?>">
                Ver más
            </a>
        </div>
    </div>

 </div>
