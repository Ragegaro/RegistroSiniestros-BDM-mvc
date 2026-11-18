<?php /* Listado de pólizas recuperadas por PolizaC. */ ?>
<div class="page-heading"><h1>Mis pólizas</h1><p>Vehículos y coberturas asociados a tu cuenta.</p></div>
<section class="contenedor-multiple">
    <?php if (empty($misPolizas)): ?>
        <div class="empty-state"><h2>Sin pólizas registradas</h2><p>Cuando tengas una póliza asociada aparecerá aquí.</p></div>
    <?php else: foreach ($misPolizas as $poliza): ?>
        <article class="P-S-individual">
            <div class="info">
                <span class="card-number">Póliza <?php echo htmlspecialchars($poliza->Poliza); ?></span>
                <strong>Vehículo:</strong> <?php echo htmlspecialchars($poliza->Marca . ' ' . $poliza->Modelo); ?><br>
                <strong>Aseguradora:</strong> <?php echo htmlspecialchars($poliza->Aseguradora); ?>
            </div>
            <div class="acciones"><span class="status-badge">Registrada</span></div>
        </article>
    <?php endforeach; endif; ?>
</section>
