<?php /* Panel principal para el rol asegurado. */ ?>
<section class="welcome-card">
    <p>Panel del asegurado</p>
    <h1>Hola, <?php echo htmlspecialchars($_SESSION['alias']); ?></h1>
    <p>Gestiona tus pólizas y reportes desde un solo lugar.</p>
</section>

<section class="quick-actions" aria-label="Acciones rápidas">
    <a class="action-card primary" href="<?php echo urlsite ?>?page=report">
        <strong>Reportar un siniestro</strong>
        <span>Registra los datos y adjunta tus evidencias.</span>
    </a>
    <a class="action-card" href="<?php echo urlsite ?>?page=polizas">
        <strong>Ver pólizas</strong>
        <span>Consulta tus vehículos asegurados.</span>
    </a>
    <a class="action-card" href="<?php echo urlsite ?>?page=misSiniestros">
        <strong>Mis siniestros</strong>
        <span>Revisa el avance de tus reportes.</span>
    </a>
</section>
