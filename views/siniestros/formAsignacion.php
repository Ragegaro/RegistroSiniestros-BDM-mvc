<?php
/* Reutilizado en el menú y en el expediente. $casoAsignacion siempre es un array. */
$idCaso = (int) $casoAsignacion['id'];
$idActual = (int) ($casoAsignacion['ajustador_id'] ?? 0);
$cerrado = strcasecmp(trim($casoAsignacion['Estatus'] ?? ''), 'Cerrado') === 0;
$idsActivos = array_map('intval', array_column($ajustadores, 'id'));
?>
<?php if ($cerrado): ?>
    <p class="helper">Este siniestro está cerrado; su asignación no se puede modificar.</p>
<?php else: ?>
    <form class="assignment-form" action="<?php echo urlsite ?>?page=asignarAjustador" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo Seguridad::escapar(Seguridad::token()); ?>">
        <input type="hidden" name="id_siniestro" value="<?php echo $idCaso; ?>">
        <input type="hidden" name="volver" value="<?php echo Seguridad::escapar($volverAsignacion); ?>">
        <div class="form-group-app">
            <label for="ajustador-<?php echo $idCaso; ?>">Responsable del siniestro #<?php echo $idCaso; ?></label>
            <select id="ajustador-<?php echo $idCaso; ?>" name="ajustador_id" required>
                <option value="" <?php echo $idActual === 0 ? 'selected' : ''; ?> disabled>Selecciona un ajustador activo</option>
                <?php if ($idActual > 0 && !in_array($idActual, $idsActivos, true)): ?>
                    <option value="" selected disabled>Responsable actual inactivo o sin rol de ajustador</option>
                <?php endif; ?>
                <?php foreach ($ajustadores as $ajustador): ?>
                    <option value="<?php echo (int) $ajustador['id']; ?>" <?php echo $idActual === (int) $ajustador['id'] ? 'selected' : ''; ?>><?php
                        echo Seguridad::escapar($ajustador['nombre'] . ' (@' . $ajustador['alias'] . ') · ' . $ajustador['casos'] . ' caso(s)');
                    ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit">Guardar asignación</button>
    </form>
<?php endif; ?>
