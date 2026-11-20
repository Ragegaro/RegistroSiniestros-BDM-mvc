<?php
/* Alta de póliza para el asegurado que inició sesión. */
$valor = static function ($campo) use ($anterior) {
    return Seguridad::escapar($anterior[$campo] ?? '');
};
$aseguradoraElegida = (string) ($anterior['aseguradora_id'] ?? (empty($aseguradoras) ? 'nueva' : ''));
$modoVehiculo = !empty($vehiculos) && ($anterior['vehiculo_modo'] ?? '') === 'existente' ? 'existente' : 'nuevo';
?>
<section class="app-card policy-form-card">
    <div class="page-heading">
        <span class="card-number">Mis pólizas / Nuevo registro</span>
        <h1>Registrar póliza</h1>
        <p>Agrega tu seguro de automóvil. Después podrás seleccionarlo al reportar un siniestro.</p>
    </div>

    <form action="<?php echo urlsite; ?>?page=guardarPoliza" method="post" id="registro-poliza">
        <input type="hidden" name="csrf_token" value="<?php echo Seguridad::escapar(Seguridad::token()); ?>">

        <h2 class="policy-section-heading"><span>1</span> Datos de tu seguro</h2>
        <div class="form-grid">
            <div class="form-group-app">
                <label for="numero_poliza">Número de póliza</label>
                <input type="text" id="numero_poliza" name="numero_poliza" value="<?php echo $valor('numero_poliza'); ?>" maxlength="50" placeholder="Ej. AUTO-2026-00125" required autocomplete="off">
            </div>
            <div class="form-group-app">
                <label for="aseguradora_id">Aseguradora</label>
                <select id="aseguradora_id" name="aseguradora_id" required>
                    <option value="" <?php echo $aseguradoraElegida === '' ? 'selected' : ''; ?>>Selecciona una aseguradora</option>
                    <?php foreach ($aseguradoras as $aseguradora): ?>
                        <option value="<?php echo (int) $aseguradora->id; ?>" <?php echo $aseguradoraElegida === (string) $aseguradora->id ? 'selected' : ''; ?>><?php echo Seguridad::escapar($aseguradora->nombre); ?></option>
                    <?php endforeach; ?>
                    <option value="nueva" <?php echo $aseguradoraElegida === 'nueva' ? 'selected' : ''; ?>>Mi aseguradora no aparece en la lista</option>
                </select>
            </div>
            <div class="form-group-app full" id="bloque-aseguradora-nueva">
                <label for="aseguradora_nueva">Nombre de la aseguradora</label>
                <input type="text" id="aseguradora_nueva" name="aseguradora_nueva" value="<?php echo $valor('aseguradora_nueva'); ?>" maxlength="100" placeholder="Escribe el nombre como aparece en tu póliza">
                <p class="helper">Si no existe en el catálogo, se agregará automáticamente.</p>
            </div>
        </div>

        <h2 class="policy-section-heading"><span>2</span> Vehículo asegurado</h2>
        <div class="form-grid">
            <div class="form-group-app full">
                <label for="vehiculo_modo">¿Qué vehículo cubre esta póliza?</label>
                <select name="vehiculo_modo" id="vehiculo_modo" required>
                    <option value="nuevo" <?php echo $modoVehiculo === 'nuevo' ? 'selected' : ''; ?>>Registrar un vehículo nuevo</option>
                    <?php if (!empty($vehiculos)): ?>
                        <option value="existente" <?php echo $modoVehiculo === 'existente' ? 'selected' : ''; ?>>Utilizar uno de mis vehículos registrados</option>
                    <?php endif; ?>
                </select>
                <p class="helper">Puedes asegurar el mismo vehículo con otra póliza sin capturar sus datos otra vez.</p>
            </div>

            <div class="form-group-app full" id="bloque-vehiculo-existente">
                <label for="vehiculo_id">Selecciona tu vehículo</label>
                <select name="vehiculo_id" id="vehiculo_id">
                    <option value="">Selecciona un vehículo</option>
                    <?php foreach ($vehiculos as $vehiculo): ?>
                        <option value="<?php echo (int) $vehiculo->id; ?>" <?php echo (int) ($anterior['vehiculo_id'] ?? 0) === (int) $vehiculo->id ? 'selected' : ''; ?>><?php echo Seguridad::escapar($vehiculo->marca . ' ' . $vehiculo->modelo . ' · ' . $vehiculo->placas); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div id="bloque-vehiculo-nuevo" class="form-grid policy-vehicle-grid">
            <div class="form-group-app">
                <label for="marca">Marca</label>
                <input type="text" id="marca" name="marca" value="<?php echo $valor('marca'); ?>" maxlength="30" placeholder="Ej. Nissan" required>
            </div>
            <div class="form-group-app">
                <label for="modelo">Modelo</label>
                <input type="text" id="modelo" name="modelo" value="<?php echo $valor('modelo'); ?>" maxlength="50" placeholder="Ej. Versa" required>
            </div>
            <div class="form-group-app">
                <label for="color">Color <span class="helper">(opcional)</span></label>
                <input type="text" id="color" name="color" value="<?php echo $valor('color'); ?>" maxlength="30" placeholder="Ej. Gris">
            </div>
            <div class="form-group-app">
                <label for="placas">Placas</label>
                <input type="text" id="placas" name="placas" value="<?php echo $valor('placas'); ?>" minlength="3" maxlength="10" pattern="[A-Za-z0-9\-]{3,10}" placeholder="Ej. ABC-123" required>
            </div>
            <div class="form-group-app full">
                <label for="serie">Número de serie (VIN)</label>
                <input type="text" id="serie" name="serie" value="<?php echo $valor('serie'); ?>" minlength="5" maxlength="50" pattern="[A-Za-z0-9\-]{5,50}" placeholder="Escríbelo como aparece en tu tarjeta de circulación" required>
                <p class="helper">El VIN puede contener letras y números. No lo captures como un número entero.</p>
            </div>
        </div>

        <div class="policy-info-note">
            <strong>Importante:</strong> Este registro vincula la información que proporcionas con tu cuenta. No comprueba la vigencia de tu seguro ante la compañía.
        </div>
        <div class="form-actions">
            <button type="submit">Guardar póliza</button>
            <a class="btn btn-outline-app" href="<?php echo urlsite; ?>?page=polizas">Cancelar</a>
        </div>
    </form>
</section>
<script src="<?php echo urlsite; ?>assets/js/polizas.js"></script>
