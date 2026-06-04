<button disabled="disabled">CHAT </button>
<div>
   
    <section>
        <h2>Datos del siniestro</h2><br>
        <strong>No. de siniestro: </strong> <?php echo $siniestro['id']; ?> <br>
        <strong>No. de Poliza:</strong> <?php echo $siniestro['Poliza']; ?> <strong> Aseguradora: </strong> <?php echo $siniestro['Aseguradora']; ?>   <br>  
            <?php if ($_SESSION['rol_slug'] !== 'asegurado'): ?>
                <strong>Nombre del Asegurado:</strong> <?php echo $siniestro['nombreCliente']; ?><br>
            <?php endif; ?>
        <strong>Auto: </strong> <?php echo $siniestro['Marca']; ?>  <strong> Modelo: </strong> <?php echo $siniestro['Modelo']; ?></strong><br>
        <strong>Ajustador: </strong> <?php echo $siniestro['Ajustador']; ?><br>
        <strong>Ubicacion: </strong> <?php echo $siniestro['direccion']; ?><br>
        <strong>Fecha:   </strong> <?php echo $siniestro['fecha']; ?>  <strong> Hora: </strong> <?php echo $siniestro['hora']; ?></strong><br>
        <strong>Estatus:</strong> <?php echo $siniestro['Estatus']; ?><br>
        <strong>Descripción: </strong> <?php echo $siniestro['descripcion']; ?> 

    </section>
    <!--    -->
   <div class="evidencias">
        <h2>Evidencias actuales</h2>
        <?php if (!empty($siniestro['multimedia'])): ?>
            
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
            <?php foreach ($siniestro['multimedia'] as $media): ?>
                
                <div class="media-item" style="border: 1px solid #ccc; padding: 10px;">
                    <?php if ($media->tipo === 'Foto'):                         
                        // Convertir los bytes del BLOB a Base64 para HTML
                        $base64 = base64_encode($media->archivo);
                        $imgSrc = 'data:image/jpeg;base64,' . $base64; 
                    ?>
                    <img src="<?php echo $imgSrc; ?>" alt="Evidencia" style="max-width: 300px; max-height: 250px; display: block;">
                    <!--<p style="text-align: center; margin-top: 5px;">Foto</p>-->
                    
                    <?php elseif ($media->tipo === 'Video'): ?>
                        <video width="300" height="250" controls style="display: block;">
                            <source src="<?php echo $media->ruta; ?>" type="video/mp4">
                            Tu navegador no soporta el tag de video.
                        </video>
                        <!--<p style="text-align: center; margin-top: 5px;">Video</p>-->
                    <?php endif; ?>
                </div>

            <?php endforeach; ?>
            </div>

        <?php else: ?>
            <p>No hay fotos o videos registrados aún.</p>
        <?php endif; ?>
    </div>



    <!--    -->
    <h3>Historial de Estatus</h3>
    <ul>
        <?php foreach($historial as $h): ?>
            <li>
                <strong><?php echo $h->Estatus_Nuevo; ?></strong> 
                <small><?php echo $h->Fecha_Cambio; ?></small>
            </li>
        <?php endforeach; ?>
    </ul>

    <hr>
    <!--ACTUALIZAR ESTATUS-->
    <?php if(isset($_SESSION['rol_slug']) && ($_SESSION['rol_slug'] === 'admin' || $_SESSION['rol_slug'] === 'supervisor')): ?>
        <h3>Actualizar Estatus</h3>
        
        <form action="<?php echo urlsite ?>?page=actualizarEstatus" method="POST">
            <input type="hidden" name="id_siniestro" value="<?php echo $siniestro['id']; ?>">
            
            <label>Nuevo Estatus:</label>
            <select name="nuevo_estatus" id="nuevo_estatus" class="form-control">
                <option value="">Seleccione un Estatus</option>
                <?php foreach ($listaEstatus as $estatusItem): ?>
                    <option value="<?php echo $estatusItem['id']; ?>" 
                        <?php echo ($siniestro['Estatus'] === $estatusItem['nombre']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($estatusItem['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <br>
            
            <button type="submit">Guardar Cambio</button>
        </form>
    <?php endif; ?>

</div>



<!--Aqui por medio del controlador se activa o desactiva lo siguiente-->


<?php if ($_SESSION['rol_slug'] === 'ajustador' || $_SESSION['rol_slug'] === 'supervisor'): ?>
    <h2>Evaluación del Ajustador</h2>
    <section class="card-evaluacion" style="border: 1px solid #ccc; padding: 20px; margin-bottom: 20px;">
        
        <?php if (empty($siniestro['diagnostico'])): ?>
            <form action="index.php?page=guardarEvaluacion" method="POST">
                <input type="hidden" name="id_siniestro" value="<?php echo $siniestro['id']; ?>">
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display:block; margin-bottom: 5px; font-weight:bold;">Diagnóstico Preliminar</label>
                    <textarea name="diagnostico" required class="form-control" style="width:100%; height:100px;"></textarea>
                </div>
                
                <button type="submit" name="btnEvaluacion" class="btn-pro">Guardar evaluación</button>
            </form>

        <?php else: ?>
            <div class="diagnostico-guardado" style="margin-bottom: 20px;">
                <p><strong>Diagnóstico Registrado:</strong></p>
                <div style="background-color: #f9f9f9; padding: 15px; border-left: 4px solid #007bff; font-style: italic;">
                    <?php echo nl2br(htmlspecialchars($siniestro['diagnostico'])); ?>
                </div>
            </div>

            <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">

            <div class="subir-evidencias">
                <p><strong>📂 Subir Nuevas Evidencias al Caso</strong></p>
                <form action="index.php?page=subirEvidenciaAdicional" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id_siniestro" value="<?php echo $siniestro['id']; ?>">
                    
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label style="display:block; margin-bottom: 5px;">Seleccione archivos (Fotos/Videos):</label>
                        <input type="file" name="archivos_evidencia[]" multiple accept="image/*,video/*" required id="js_input_evidencia">
                    </div>
                    
                    <div id="js_preview_container" style="display:flex; gap:10px; flex-wrap:wrap; margin-top:10px;"></div>
                    
                    <button type="submit" name="btnSubirEvidencia" class="btn-pro" style="margin-top:10px;">Cargar Evidencias</button>
                </form>
            </div>
        <?php endif; ?>

    </section>
<?php endif; ?>


<script>
document.getElementById('js_input_evidencia').addEventListener('change', function(event) {
    const contenedor = document.getElementById('js_preview_container');
    contenedor.innerHTML = ''; 
    const archivos = event.target.files;
    
    for (let i = 0; i < archivos.length; i++) {
        const file = archivos[i];
        
  
        if (!file.type.startsWith('image/') && !file.type.startsWith('video/')) {
            alert('¡Error! El archivo "' + file.name + '" no es una imagen ni un video permitido.');
            event.target.value = ''; 
            contenedor.innerHTML = '';
            return;
        }
        
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const wrapper = document.createElement('div');
            wrapper.style.width = '100px';
            wrapper.style.height = '100px';
            wrapper.style.border = '1px solid #ddd';
            wrapper.style.overflow = 'hidden';
            
            if (file.type.startsWith('image/')) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.width = '100%';
                img.style.height = '100%';
                img.style.objectFit = 'cover';
                wrapper.appendChild(img);
            } else if (file.type.startsWith('video/')) {
                const video = document.createElement('video');
                video.src = e.target.result;
                video.style.width = '100%';
                video.style.height = '100%';
                video.style.objectFit = 'cover';
                video.muted = true;
                wrapper.appendChild(video);
            }
            
            contenedor.appendChild(wrapper);
        };
        reader.readAsDataURL(file);
    }
});
</script>
