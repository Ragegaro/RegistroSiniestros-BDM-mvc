<?php /* $mensaje proviene de ChatM; se muestra como texto, nunca como HTML. */ ?>
<article class="chat-message<?php echo (int) $mensaje['usuario_id'] === (int) $_SESSION['id_usuario'] ? ' is-mine' : ''; ?>" data-id="<?php echo (int) $mensaje['id']; ?>">
    <div class="chat-message-meta">
        <strong><?php echo Seguridad::escapar($mensaje['autor']); ?></strong>
        <span class="chat-role"><?php echo Seguridad::escapar($mensaje['rol_nombre']); ?></span>
        <?php if ((int) $mensaje['usuario_id'] === (int) $_SESSION['id_usuario']): ?><span class="helper">Tú</span><?php endif; ?>
    </div>
    <p class="chat-message-text"><?php echo Seguridad::escapar($mensaje['mensaje']); ?></p>
    <time class="chat-message-time"><?php echo Seguridad::escapar($mensaje['fecha']); ?></time>
</article>
