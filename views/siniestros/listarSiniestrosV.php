<?php /* Listado adaptable al rol. */ ?>
<div class="page-heading">
    <h1>Siniestros</h1>
    <p>Consulta el estado y los detalles de cada reporte.</p>
</div>
<section class="cards-grid">
    <?php if (empty($misSiniestros)): ?>
        <div class="empty-state"><h2>Todo tranquilo por aquí</h2><p>No hay siniestros para mostrar.</p></div>
    <?php else: foreach ($misSiniestros as $siniestro): ?>
        <article class="claim-card">
            <div class="info">
                <span class="card-number">Siniestro #<?php echo (int) $siniestro->id; ?></span>
                <strong>Póliza:</strong> <?php echo htmlspecialchars($siniestro->Poliza); ?><br>
                <?php if (($_SESSION['rol_slug'] ?? '') !== 'asegurado' && isset($siniestro->nombreCliente)): ?>
                    <strong>Asegurado:</strong> <?php echo htmlspecialchars($siniestro->nombreCliente); ?><br>
                <?php endif; ?>
                <strong>Modelo:</strong> <?php echo htmlspecialchars($siniestro->Modelo); ?><br>
                <strong>Fecha:</strong> <?php echo htmlspecialchars($siniestro->fecha); ?>
            </div>
            <div class="acciones">
                <span class="status-badge"><?php echo htmlspecialchars($siniestro->Estatus); ?></span>
                <a class="vermas" href="<?php echo urlsite ?>?page=detalle&amp;id=<?php echo (int) $siniestro->id; ?>">Ver detalles</a>
            </div>
        </article>
    <?php endforeach; endif; ?>
</section>
