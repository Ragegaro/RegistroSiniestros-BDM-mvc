<button disabled="disabled">CHAT </button>
<div>
   
    <section>
        <h2>Datos del siniestro</h2><br>
        <strong>No. de siniestro: </strong> <?php echo $siniestro['id']; ?> <br>
        <strong>No. de Poliza:</strong> <?php echo $siniestro['Poliza']; ?> <strong> Aseguradora: </strong> <?php echo $siniestro['Aseguradora']; ?>   <br>  
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
                <strong><?php echo $h->estatus_id; ?></strong> 
                <small><?php echo $h->fecha_actu; ?></small>
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
            <select name="estatus" required>
                <option value="En Evaluación">En Evaluación</option>
                <option value="Aprobado">Aprobado</option>
                <option value="Rechazado">Rechazado</option>
                <option value="Pago en Proceso">Pago en Proceso</option>
                <option value="Cerrado">Cerrado</option>
            </select>
            <br>
            
            <label>Comentario o Justificación:</label><br>
            <textarea name="comentario" rows="3" required placeholder="Motivo del cambio..."></textarea>
            <br>
            
            <button type="submit">Guardar Cambio</button>
        </form>
    <?php endif; ?>

</div>



<!--Aqui por medio del controlador se activa o desactiva lo siguiente-->


  <?php if ($_SESSION['rol_slug'] === 'ajustador' || $_SESSION['rol_slug'] === 'supervisor'): ?>
        <h2>Evaluación del Ajustador</h2>
        <section>
            <form action="index.php?page=guardarEvaluacion" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id_siniestro" value="<?php echo $siniestro['id']; ?>">
                
                <label>Diagnóstico preliminar</label>
                <textarea name="diagnostico" required></textarea>
                
                <button type="submit" name="btnEvaluacion">Guardar evaluación</button>
            </form>
        </section>
    <?php endif; ?>

<!-- OTRA VERSION DE LA EVALUACION DEL FORMULARIO (de seguro esto se irá)-->
<!--
    <h2>Evaluación del Ajustador</h2>
    <section>
        <form action="index.php?page=" method="POST" enctype="multipart/form-data" class="form-ajustador">

            <label>Diagnóstico pollo</label>
            <textarea name="diagnostico" rows="4" required></textarea>
            
            <label>Monto estimado de daños ($)</label>
            <input type="number" name="monto" step="0.01" required>

            <br>

            <label>Estado del siniestro</label>
            <select name="estatus" required>
                <option value="En proceso"></option>
            </select>
            <br>

            <label>Evidencia del siniestro</label>
            <input type="file" name="evidencias[]" multiple accept="image/*, video/*">

            <button type="submit" name="btnEvaluacion">Guardar evaluación</button>
        </form>
    </section>
    -->