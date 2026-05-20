<?php
    require "views/layouts/header.php";
    require "views/layouts/navbar.php";
    require "controllers/LoginC.php";
    require_once "controllers/PolizaC.php";

    
    $misPolizas = PolizaC::listarPolizas();

?>

    <div class="polizas">
        <h1>Mis polizas</h1>
        <!--<button  disabled="disabled"> Agregar polizas</button>-->
    </div>   

    <div class="contenedor-multiple">
        <?php if (empty($misPolizas)): ?>
            <p>Aún no tienes pólizas registradas.</p>
        <?php else:
              foreach($misPolizas as $poliza): ?>

        <div class="P-S-individual">
            <div class="info">
                <p>
                    <strong>No. de Póliza:</strong> <?php echo $poliza->Poliza; ?> <br>    
                        <strong>Marca:</strong> <?php echo $poliza->Marca; ?> <br> 
                        <strong>Modelo:</strong> <?php echo $poliza->Modelo; ?><br>
                        <strong>Aseguradora:</strong> <?php echo $poliza->Aseguradora; ?> <br>

                <!--Certificado <br>-->
                <!--Estatus:<br>-->
                </p>
            </div>

            <div class="acciones">
                <div class="estatus">
                    <button class="btn"> Eliminar</button>
                </div>
                <a class="vermas" href="<?php echo urlsite?>?page=agregarAuto">
                    Ver más
                </a>
            </div>

        </div>
        <?php endforeach; 
        endif; ?>                



    </div>    
<!---->