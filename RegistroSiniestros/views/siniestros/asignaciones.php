<div class="page-heading">
    <h1>Asignar ajustadores</h1>
    <p>El supervisor elige qué ajustador atenderá cada siniestro del asegurado.</p>
</div>
<nav class="assignment-filters" aria-label="Filtrar siniestros">
    <a class="btn <?php echo $filtro === 'todos' ? '' : 'btn-outline-app'; ?>" href="<?php echo urlsite ?>?page=asignaciones">Todos</a>
    <a class="btn <?php echo $filtro === 'pendientes' ? '' : 'btn-outline-app'; ?>" href="<?php echo urlsite ?>?page=asignaciones&amp;filtro=pendientes">Sin asignar</a>
</nav>
<?php if (!$ajustadores): ?>
    <p class="alert-app error">No hay ajustadores activos. Una cuenta debe tener el rol <code>ajustador</code> y estatus <code>1</code> para aparecer aquí. Registrar una cuenta normal crea un asegurado, no un ajustador.</p>
<?php endif; ?>
<section class="assignment-grid">
<?php if (!$casos): ?>
    <p class="empty-state">No hay siniestros para este filtro.</p>
<?php else: foreach ($casos as $caso): ?>
    <article class="app-card assignment-card">
        <div class="assignment-title"><h2>Siniestro #<?php echo (int) $caso->id; ?></h2><span class="status-badge"><?php echo Seguridad::escapar($caso->Estatus); ?></span></div>
        <p><strong>Asegurado:</strong> <?php echo Seguridad::escapar($caso->nombreCliente); ?><br>
        <strong>Póliza:</strong> <?php echo Seguridad::escapar($caso->Poliza); ?><br>
        <strong>Responsable actual:</strong> <?php echo Seguridad::escapar($caso->Ajustador); ?></p>
        <?php $casoAsignacion = (array) $caso; $volverAsignacion = 'asignaciones'; require 'views/siniestros/formAsignacion.php'; ?>
        <a class="assignment-detail" href="<?php echo urlsite ?>?page=detalle&amp;id=<?php echo (int) $caso->id; ?>">Abrir expediente →</a>
    </article>
<?php endforeach; endif; ?>
</section>
