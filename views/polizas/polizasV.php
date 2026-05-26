<?php
    /*require "views/layouts/header.php";
    require "views/layouts/navbar.php";
    require "controllers/LoginC.php";
    require_once "controllers/PolizaC.php";

    
    $misPolizas = PolizaC::listarPolizas();*/

?>

    <div>
        <h1>Mis polizas</h1>
        <!--<button  disabled="disabled"> Agregar polizas</button>-->
    </div>   

    <div>
        <?php if (empty($misPolizas)): ?>
            <p>Aún no tienes pólizas registradas.</p>
        <?php else:
            foreach($misPolizas as $poliza): 
        ?>

        <div>
            <div>
                <strong>No. de Póliza:</strong> <?php echo $poliza->Poliza; ?> <br>    
                <strong>Marca:</strong> <?php echo $poliza->Marca; ?> <br> 
                <strong>Modelo:</strong> <?php echo $poliza->Modelo; ?><br>
                <strong>Aseguradora:</strong> <?php echo $poliza->Aseguradora; ?> <br>
                <strong>Fecha de Inicio:</strong> <?php echo $poliza->FechaInicio; ?> <br>
            </div>

            <div>
                <div>
                    <button> Eliminar</button>
                </div>
                <a href="<?php echo urlsite?>?page=detallePoliza&id=<?php echo $poliza->id; ?>">
                    Ver más
                </a>
            </div>

        </div>
        
        <?php endforeach; 
        endif; ?>                



    </div>   