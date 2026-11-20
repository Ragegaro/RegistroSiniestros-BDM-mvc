<?php
$rol = $_SESSION['rol_slug'] ?? '';
$inicio = $rol !== '' ? $rol : 'logIn';
?>
<div class="navbar-shell">
    <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="main-navigation">☰ Menú</button>
    <nav class="Navbar" id="main-navigation" aria-label="Navegación principal">
        <a href="<?php echo urlsite ?>?page=<?php echo htmlspecialchars($inicio); ?>">Inicio</a>

        <?php if ($rol === 'asegurado'): ?>
            <a href="<?php echo urlsite ?>?page=report">Reportar siniestro</a>
            <a href="<?php echo urlsite ?>?page=misSiniestros">Siniestros</a>
            <a href="<?php echo urlsite ?>?page=polizas">Pólizas</a>
            <a href="<?php echo urlsite ?>?page=agregarPoliza">Registrar póliza</a>
        <?php elseif ($rol === 'ajustador'): ?>
            <a href="<?php echo urlsite ?>?page=misSiniestros">Siniestros asignados</a>
        <?php elseif ($rol === 'supervisor' || $rol === 'admin'): ?>
            <a href="<?php echo urlsite ?>?page=misSiniestros">Siniestros</a>
            <a href="<?php echo urlsite ?>?page=asignaciones">Asignar ajustadores</a>
        <?php endif; ?>

        <a href="<?php echo urlsite ?>?page=miPerfil">Mi perfil</a>
        <a class="logout" href="<?php echo urlsite ?>?page=logout">Cerrar sesión</a>
    </nav>
</div>
