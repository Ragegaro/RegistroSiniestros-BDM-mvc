<?php
$rolActual = $_SESSION['rol_slug'] ?? '';
$esSupervisor = in_array($rolActual, ['supervisor', 'admin'], true);
?>
<section class="welcome-card">
    <p><?php echo $esSupervisor ? 'Panel de supervisión' : 'Panel de seguimiento'; ?></p>
    <h1>Hola, <?php echo htmlspecialchars($_SESSION['alias']); ?></h1>
    <p><?php echo $esSupervisor
        ? 'Consulta los reportes y mantén actualizado su estado.'
        : 'Consulta los reportes asignados y mantén actualizado su seguimiento.'; ?></p>
</section>

<section class="quick-actions two-actions">
    <a class="action-card primary" href="<?php echo urlsite ?>?page=misSiniestros">
        <strong>Revisar siniestros</strong><span>Consulta expedientes y evidencias.</span>
    </a>
    <a class="action-card" href="<?php echo urlsite ?>?page=miPerfil">
        <strong>Mi perfil</strong><span>Actualiza tus datos de acceso.</span>
    </a>
</section>

<?php if ($esSupervisor && !empty($datosDashboard)): ?>
<section class="dashboard-grid" aria-label="Resumen de supervisión">
    <article class="dashboard-card">
        <h2>Resumen de casos</h2>
        <?php if (empty($datosDashboard['resumen_estatus'])): ?>
            <p class="helper">No hay datos disponibles.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($datosDashboard['resumen_estatus'] as $est): ?>
                    <li><span><?php echo htmlspecialchars($est['Estatus_Siniestro']); ?></span><strong><?php echo (int) $est['Cantidad_Total']; ?></strong></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </article>
    <article class="dashboard-card">
        <h2>Carga de ajustadores</h2>
        <?php if (empty($datosDashboard['ajustadores'])): ?>
            <p class="helper">No hay ajustadores registrados.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($datosDashboard['ajustadores'] as $ajus): ?>
                    <li><span><?php echo htmlspecialchars($ajus['Nombre_Ajustador']); ?></span><strong><?php echo (int) $ajus['Siniestros_Asignados']; ?></strong></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </article>
    <article class="dashboard-card">
        <h2>Clientes</h2>
        <?php if (empty($datosDashboard['clientes'])): ?>
            <p class="helper">No hay clientes registrados.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($datosDashboard['clientes'] as $cli): ?>
                    <li><span><?php echo htmlspecialchars($cli['Nombre_Completo']); ?></span><strong><?php echo (int) $cli['Total_Polizas']; ?> pól.</strong></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </article>
</section>
<?php endif; ?>
