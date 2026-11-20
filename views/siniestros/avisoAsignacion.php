<?php if (!empty($_SESSION['aviso_asignacion'])):
    $aviso = $_SESSION['aviso_asignacion'];
    unset($_SESSION['aviso_asignacion']); ?>
    <p role="status" class="alert-app <?php echo $aviso['tipo'] === 'success' ? 'success' : 'error'; ?>"><?php echo Seguridad::escapar($aviso['texto']); ?></p>
<?php endif; ?>
