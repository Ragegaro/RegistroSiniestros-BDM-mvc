<?php
require "views/layouts/header.php";
?>


<h1>Mis polizas</h1>
<button> Agregar polizas</button>

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
            
            <a href="">Eliminar</a>
            <a class="vermas" href="?page=detalle&id=<?php echo $siniestro['id']; ?>">
                Ver más numero 2
            </a>
            
         </div>
    </div>

</div>