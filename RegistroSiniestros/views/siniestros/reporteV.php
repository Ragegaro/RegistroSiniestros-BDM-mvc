<?php /* Alta de un siniestro y carga múltiple de evidencias. */ ?>
<section class="app-card">
    <div class="page-heading"><h1>Reportar siniestro</h1><p>Proporciona la información del incidente y adjunta evidencia clara.</p></div>
    <form action="<?php echo urlsite ?>?page=guardarSiniestro" method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo Seguridad::escapar(Seguridad::token()); ?>">
        <input type="hidden" name="MAX_FILE_SIZE" value="26214400">
        <div class="form-grid">
            <div class="form-group-app full">
                <label for="poliza">Número de póliza</label>
                <select name="SelectPoliza" id="poliza" required>
                    <option value="">Selecciona una póliza</option>
                    <?php foreach ($misPolizas as $p): ?>
                        <option value="<?php echo (int) $p->id; ?>" <?php echo (int) ($anterior['SelectPoliza'] ?? 0) === (int) $p->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($p->Poliza . ' · ' . $p->Marca . ' ' . $p->Modelo); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group-app full"><label for="ubicacion">Dirección del incidente</label><input id="ubicacion" type="text" name="ubicacion" value="<?php echo Seguridad::escapar($anterior['ubicacion'] ?? ''); ?>" required></div>
            <div class="form-group-app"><label for="fecha">Fecha</label><input id="fecha" type="date" name="fecha" value="<?php echo Seguridad::escapar($anterior['fecha'] ?? ''); ?>" required></div>
            <div class="form-group-app"><label for="hora">Hora</label><input id="hora" type="time" name="hora" value="<?php echo Seguridad::escapar($anterior['hora'] ?? ''); ?>" required></div>
            <div class="form-group-app full"><label for="input-evidencias">Evidencias <span class="helper">(fotos o videos)</span></label><input type="file" name="evidencias[]" id="input-evidencias" multiple accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,video/quicktime,video/ogg"><p class="helper">Hasta 10 archivos, 25 MB por archivo y 50 MB en total. Para reproducción en navegador, utiliza preferentemente MP4 con H.264/AAC o WEBM.</p><div id="preview-container" aria-live="polite"></div></div>
            <div class="form-group-app full"><label for="descripcion">Descripción del suceso</label><textarea id="descripcion" name="descripcion" rows="5" required placeholder="Explica brevemente qué ocurrió..."><?php echo Seguridad::escapar($anterior['descripcion'] ?? ''); ?></textarea></div>
        </div>
        <div class="form-actions"><button type="submit">Enviar reporte</button><a class="btn btn-outline-app" href="<?php echo urlsite ?>?page=misSiniestros">Cancelar</a></div>
    </form>
</section>
<script src="<?php echo urlsite ?>assets/js/preview.js"></script>
