<?php 
//$id_siniestro = isset($_GET['id']) ? $_GET['id'] : 0;
?>

<h2>Evaluación del Ajustador</h2>
<form action="guardar_evaluacion.php" method="POST" 
      class="form-ajustador">

    <!--<input type="hidden" name="id_siniestro" value="<?php //echo $id_siniestro; ?>>-->
    <label>Diagnóstico preliminar</label>
    <textarea name="diagnostico" rows="4" required></textarea>
    
    <br>

    <label>Monto estimado de daños ($)</label>
    <input type="number" name="monto" step="0.01" required>

    <br>

    <label>Estado del siniestro</label>
    <select name="estatus" required>
        <option value="En proceso">En proceso</option>
        <option value="Aprobado">Aprobado</option>
        <option value="Rechazado">Rechazado</option>
        <option value="Cerrado">Cerrado</option>
    </select>
     
    <br>

    <label>Evidencia del siniestro</label>
    <input type="file" name="fotos[]" multiple accept="image/*, video/*">

    <br>

    <button type="submit">Guardar evaluación</button>

</form>