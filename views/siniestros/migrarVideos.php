<div class="page-heading">
    <h1>Videos anteriores → BLOB</h1>
    <p>Convierte los videos que todavía dependen de un archivo en uploads/siniestros.</p>
</div>
<section class="app-card migration-diagnostics">
    <h2 class="section-title">Límite real de tu servidor</h2>
    <p><strong>max_allowed_packet:</strong> <?php echo number_format((int) $configuracion['paquete'] / 1048576, 2); ?> MB
        · <strong>Puerto:</strong> <?php echo (int) $configuracion['puerto']; ?>
        · <strong>Base:</strong> <?php echo Seguridad::escapar($configuracion['base']); ?></p>
    <details>
        <summary>Cómo aumentar el límite en XAMPP</summary>
        <p>En XAMPP abre MySQL → Config → my.ini. Dentro de <code>[mysqld]</code>, cambia la línea existente a <code>max_allowed_packet=64M</code>. Guarda, detén e inicia MySQL y recarga esta página. El valor mostrado arriba debe actualizarse.</p>
        <p class="helper">No dupliques la opción ni reemplaces todo my.ini. Si el límite ya es suficiente y la conversión falla, utiliza el código MySQL/SQLSTATE del aviso para identificar la causa.</p>
    </details>
</section>
<section class="app-card">
    <p>Los videos nuevos ya se guardan en <code>multimedia.archivo</code> (LONGBLOB). Para los anteriores, conserva la carpeta <code>uploads</code> y convierte cada video con el botón correspondiente.</p>
    <p class="helper">Se verifica el tamaño y la huella SHA-256 antes de confirmar. No se borra el archivo original ni se cambian sus permisos de acceso. Límite: 25 MB por video. Se muestran hasta 100 pendientes; al convertirlos aparecerán los siguientes.</p>
    <?php if (!$videos): ?>
        <p class="alert-app success">No hay videos pendientes de convertir.</p>
    <?php else: ?>
        <div class="migration-list">
        <?php foreach ($videos as $video): ?>
            <article class="migration-row">
                <div><strong>Video #<?php echo (int) $video['id']; ?> · Siniestro #<?php echo (int) $video['siniestro_id']; ?></strong>
                    <span class="helper"><?php echo Seguridad::escapar($video['ruta'] ?: 'Sin ruta: es necesario recuperar el archivo original.'); ?></span>
                    <span class="helper"><?php echo $video['bytes_locales'] === null ? 'Archivo original no encontrado.' : 'Tamaño del archivo: ' . number_format($video['bytes_locales'] / 1048576, 2) . ' MB'; ?></span>
                    <?php if ($video['bytes_locales'] !== null && $video['bytes_locales'] + 65536 > (int) $configuracion['paquete']): ?>
                        <strong class="migration-warning">El límite actual de MySQL es insuficiente para este archivo.</strong>
                    <?php endif; ?>
                </div>
                <form method="post" action="<?php echo urlsite ?>?page=migrarVideos">
                    <input type="hidden" name="csrf_token" value="<?php echo Seguridad::escapar(Seguridad::token()); ?>">
                    <input type="hidden" name="id_multimedia" value="<?php echo (int) $video['id']; ?>">
                    <button type="submit">Convertir a BLOB</button>
                </form>
            </article>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
