<?php foreach (Avisos::extraer() as $aviso): ?>
    <div class="app-notice <?php echo Seguridad::escapar($aviso['tipo']); ?>" role="<?php echo $aviso['tipo'] === 'error' ? 'alert' : 'status'; ?>">
        <div><strong><?php echo $aviso['tipo'] === 'error' ? 'Revisa lo siguiente' : ($aviso['tipo'] === 'success' ? 'Listo' : 'Aviso'); ?></strong>
            <p><?php echo Seguridad::escapar($aviso['texto']); ?></p></div>
        <button type="button" class="notice-close" aria-label="Cerrar aviso">×</button>
    </div>
<?php endforeach; ?>
