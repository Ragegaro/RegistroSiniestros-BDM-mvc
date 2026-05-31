<?php 
    /*require "views/layouts/header.php";
    require "views/layouts/navbar.php";
    require_once "controllers/SiniestroC.php";


    $siniestroC = new SiniestroC();
    $siniestro = $siniestroC->verDetalledSiniestro();

    if(!$siniestro) {
        echo "<div class='contenedor'><h2>Siniestro no encontrado.</h2></div>";
        exit;
    }*/
?>

<button disabled="disabled">CHAT </button>
<div>
   
    <div>
        <h2>Datos del siniestro</h2><br>
        <strong>No. de siniestro: </strong> <?php echo $siniestro['id']; ?> <br>
        <strong>No. de Poliza:</strong> <?php echo $siniestro['Poliza']; ?> <strong> Aseguradora: </strong> <?php echo $siniestro['Aseguradora']; ?>   <br>  
        <strong>Auto: </strong> <?php echo $siniestro['Marca']; ?>  <strong> Modelo: </strong> <?php echo $siniestro['Modelo']; ?></strong><br>
        <strong>Ajustador: </strong> <?php echo $siniestro['Ajustador']; ?><br>
        <strong>Ubicacion: </strong> <?php echo $siniestro['direccion']; ?><br>
        <strong>Fecha:   </strong> <?php echo $siniestro['fecha']; ?>  <strong> Hora: </strong> <?php echo $siniestro['hora']; ?></strong><br>
        <strong>Estatus:</strong> <?php echo $siniestro['Estatus']; ?><br>
        <strong>Descripción: </strong> <?php echo $siniestro['descripcion']; ?> 

    </div>
    

    <div>
        <h2>Seguimiento</h2>
        
        <?php// foreach?>
            <div>
                <p><?php //estatus br fecha y hora del siniestro?>Registro de siniestro</p>
            </div>
        <?//endforeach?>       
    </div>


</div>

<!--Aqui por medio del controlador se activa o desactiva lo siguiente-->



<h2>Evaluación del Ajustador</h2>
    <section>
        <form action="index.php?page=" method="POST" enctype="multipart/form-data" class="form-ajustador">

            <label>Diagnóstico preliminar</label>
            <textarea name="diagnostico" rows="4" required></textarea>
            
            <label>Monto estimado de daños ($)</label>
            <input type="number" name="monto" step="0.01" required>

            <br>

            <label>Estado del siniestro</label>
            <select name="estatus" required>
                <option value="En proceso"></option>
            </select>
            <br>

            <label>Evidencia del siniestro</label>
            <input type="file" name="evidencias[]" multiple accept="image/*, video/*">

            <button type="submit" name="btnEvaluacion">Guardar evaluación</button>
        </form>
    </section>


<?php 
/*require "views/siniestros/evaluacionV.php";
    if(isset($_SESSION['rol'])&& $_SESSION['rol_id']=== '2'){
        require "views/siniestros/evaluacionV.php";
    }
        if(isset($_SESSION['rol'])&& $_SESSION['rol_id']==='1'){
        require "views/siniestros/evaluacionV.php";
    }*/
?>