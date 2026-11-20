<?php /* Listado de pólizas recuperadas por PolizaC. */ ?>
<div class="policy-list-heading">
    <div class="page-heading"><h1>Mis pólizas</h1><p>Vehículos y seguros asociados a tu cuenta.</p></div>
    <?php if (($_SESSION['rol_slug'] ?? '') === 'asegurado'): ?>
        <a class="btn" href="<?php echo urlsite; ?>?page=agregarPoliza">+ Registrar póliza</a>
    <?php endif; ?>
</div>
<section class="contenedor-multiple">
    <?php if (empty($misPolizas)): ?>
        <div class="empty-state"><h2>Aún no tienes pólizas</h2><p>Registra tu primera póliza y su vehículo para poder reportar siniestros.</p>
            <?php if (($_SESSION['rol_slug'] ?? '') === 'asegurado'): ?><a class="btn" href="<?php echo urlsite; ?>?page=agregarPoliza">Registrar mi primera póliza</a><?php endif; ?>
        </div>
    <?php else: foreach ($misPolizas as $poliza): ?>
        <article class="P-S-individual">
            <div class="info">
                <span class="card-number">Póliza <?php echo htmlspecialchars($poliza->Poliza); ?></span>
                <strong>Vehículo:</strong> <?php echo htmlspecialchars($poliza->Marca . ' ' . $poliza->Modelo); ?><br>
                <strong>Aseguradora:</strong> <?php echo htmlspecialchars($poliza->Aseguradora); ?>
            </div>
            <div class="acciones"><span class="status-badge">Registrada</span>
                <?php if (($_SESSION['rol_slug'] ?? '') === 'asegurado'): ?><a class="policy-report-link" href="<?php echo urlsite; ?>?page=report&amp;poliza=<?php echo (int) $poliza->id; ?>">Reportar siniestro</a><?php endif; ?>
            </div>
        </article>
    <?php endforeach; endif; ?>
</section>
