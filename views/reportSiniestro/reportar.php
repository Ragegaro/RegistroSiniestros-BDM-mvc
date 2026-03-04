<?php 
$tipo = isset($_GET['tipo']) ? ucfirst($_GET['tipo']) : 'Siniestro';
?>

<h2>Reporte de <?php echo $tipo; ?></h2>

<form action="guardar_reporte.php" method="POST" class="form-reporte">

    <input type="hidden" name="tipo" value="<?php echo $tipo; ?>">

    <label>Número de póliza</label>
    <input type="text" name="poliza" required>

    <label>Ubicación del incidente</label>
    <input type="text" name="ubicacion" required>

    <label>Fecha</label>
    <input type="date" name="fecha" required>

    <label>Hora</label>
    <input type="time" name="hora" required>

    <label>Descripción</label>
    <textarea name="descripcion" rows="4" required></textarea>

    <button type="submit">Enviar reporte</button>

</form>