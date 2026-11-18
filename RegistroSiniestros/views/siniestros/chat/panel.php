<?php
$chatMensajes = $chatDatos['mensajes'];
$chatPrimero = $chatMensajes ? (int) $chatMensajes[0]['id'] : 0;
$chatUltimo = $chatMensajes ? (int) $chatMensajes[count($chatMensajes) - 1]['id'] : 0;
?>
<link rel="stylesheet" href="assets/css/chat.css?v=1">
<section class="app-card mt-4 chat-panel" id="chat" aria-labelledby="chat-title"
    data-case="<?php echo (int) $siniestro['id']; ?>"
    data-user="<?php echo (int) $_SESSION['id_usuario']; ?>"
    data-latest="<?php echo $chatUltimo; ?>" data-first="<?php echo $chatPrimero; ?>"
    data-url="index.php?page=chatMensajes">
    <div class="chat-heading">
        <div><h2 class="section-title" id="chat-title">Chat del siniestro</h2>
            <p class="helper">Un espacio para dar seguimiento a este caso.</p></div>
        <span class="chat-private">Conversación privada</span>
    </div>
    <?php if ($chatError !== ''): ?>
        <p class="app-notice error" role="alert"><?php echo Seguridad::escapar($chatError); ?></p>
        <a class="btn btn-outline-app" href="index.php?page=detalle&amp;id=<?php echo (int) $siniestro['id']; ?>#chat">Recargar expediente</a>
    <?php else: ?>
        <ul class="chat-participants" aria-label="Participantes autorizados">
            <li><strong>Asegurado</strong> <span data-participant="asegurado"><?php echo Seguridad::escapar($chatDatos['participantes']['asegurado']); ?></span></li>
            <li><strong>Ajustador</strong> <span data-participant="ajustador"><?php echo Seguridad::escapar($chatDatos['participantes']['ajustador'] ?? 'Sin asignar'); ?></span></li>
            <li><strong>Supervisión</strong> <span>Acceso al expediente</span></li>
        </ul>
        <p class="helper">Solo pueden participar el asegurado de la póliza, el ajustador asignado y los supervisores.</p>
        <a class="chat-older" data-chat-older <?php if (!$chatDatos['hay_anteriores']): ?>hidden<?php endif; ?>
            href="index.php?page=detalle&amp;id=<?php echo (int) $siniestro['id']; ?>&amp;chat_antes=<?php echo $chatPrimero; ?>#chat">Ver mensajes anteriores</a>
        <div class="chat-log" role="log" aria-label="Mensajes del siniestro" aria-live="polite" aria-relevant="additions" tabindex="0">
            <p class="chat-empty" <?php if ($chatMensajes): ?>hidden<?php endif; ?>>Aún no hay mensajes. Inicia la conversación sobre este siniestro.</p>
            <?php foreach ($chatMensajes as $mensaje) require __DIR__ . '/mensaje.php'; ?>
        </div>
        <button class="btn-outline-app chat-new" type="button" hidden>Ver mensajes nuevos ↓</button>
        <p class="chat-status helper" role="status" aria-live="polite">Los mensajes pertenecen únicamente a este expediente.</p>
        <p class="app-notice error chat-error" role="alert" hidden></p>
        <form class="chat-form" method="post" action="index.php?page=chatEnviar">
            <input type="hidden" name="csrf_token" value="<?php echo Seguridad::escapar(Seguridad::token()); ?>">
            <input type="hidden" name="id_siniestro" value="<?php echo (int) $siniestro['id']; ?>">
            <label for="chat-message">Tu mensaje</label>
            <textarea name="mensaje" id="chat-message" rows="3" maxlength="4000" required
                placeholder="Escribe una actualización o pregunta sobre el caso…"
                aria-describedby="chat-help chat-counter"><?php echo Seguridad::escapar($chatBorrador); ?></textarea>
            <div class="chat-compose-footer">
                <div><small class="helper" id="chat-help">Hasta 2000 caracteres. Ctrl+Enter para enviar.</small>
                    <small class="helper" id="chat-counter">Máximo 2000 caracteres.</small></div>
                <button type="submit">Enviar mensaje</button>
            </div>
        </form>
        <noscript><p class="helper">Puedes enviar mensajes sin JavaScript. Recarga el expediente para ver las respuestas.</p></noscript>
    <?php endif; ?>
</section>
<?php if ($chatError === ''): ?><script src="assets/js/chat.js?v=1" defer></script><?php endif; ?>
