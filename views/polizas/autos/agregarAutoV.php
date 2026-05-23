<div class="form-container">
    <h2>Registro de Auto</h2>
    <form action="<?php echo urlsite?>?page=guardarAuto" method="post">
        
        <label for="marca">Marca</label>
        <input type="text" id="marca" name="marca" required>

        <label for="modelo">Modelo</label>
        <input type="text" id="modelo" name="modelo" required>

        <label for="color">Color</label>
        <input type="text" id="color" name="color" required>

        <label for="placas">Placas</label>
        <input type="text" id="placas" name="placas" required>

        <label for="Cliente">Cliente</label>
        <input type="text" id="Cliente" name="Cliente">

        <label for="serie">Número de Serie</label>
        <input type="number" id="serie" name="serie" required>

        <button type="submit">Guardar Auto</button>
    </form>
</div>

