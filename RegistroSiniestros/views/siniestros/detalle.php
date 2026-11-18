<?php
/* Expediente completo: datos, multimedia, historial y acciones según el rol. */

$estaCerrado =
    isset($siniestro['Estatus']) &&
    strcasecmp(trim($siniestro['Estatus']), 'Cerrado') === 0;
?>

<div class="page-heading">

    <h1>
        Siniestro #<?php echo (int) $siniestro['id']; ?>
    </h1>

    <p>Expediente y seguimiento del reporte.</p>

</div>

<div class="detail-grid">

    <section class="app-card">

        <h2 class="section-title">
            Datos del siniestro
        </h2>

        <dl class="detail-list">

            <div>
                <dt>Póliza</dt>
                <dd>
                    <?php echo htmlspecialchars($siniestro['Poliza']); ?>
                </dd>
            </div>

            <div>
                <dt>Aseguradora</dt>
                <dd>
                    <?php echo htmlspecialchars($siniestro['Aseguradora']); ?>
                </dd>
            </div>

            <div>
                <dt>Vehículo</dt>
                <dd>
                    <?php
                    echo htmlspecialchars(
                        $siniestro['Marca'] . ' ' . $siniestro['Modelo']
                    );
                    ?>
                </dd>
            </div>

            <div>
                <dt>Ajustador</dt>
                <dd>
                    <?php echo htmlspecialchars($siniestro['Ajustador']); ?>
                </dd>
            </div>


            <?php if (
                ($_SESSION['rol_slug'] ?? '') !== 'asegurado' &&
                !empty($siniestro['nombreCliente'])
            ): ?>

                <div>
                    <dt>Asegurado</dt>
                    <dd>
                        <?php
                        echo htmlspecialchars(
                            $siniestro['nombreCliente']
                        );
                        ?>
                    </dd>
                </div>

            <?php endif; ?>


            <div>
                <dt>Fecha y hora</dt>
                <dd>
                    <?php
                    echo htmlspecialchars(
                        $siniestro['fecha'] .
                        ' · ' .
                        $siniestro['hora']
                    );
                    ?>
                </dd>
            </div>

            <div>
                <dt>Estatus</dt>
                <dd>
                    <span class="status-badge">
                        <?php
                        echo htmlspecialchars(
                            $siniestro['Estatus']
                        );
                        ?>
                    </span>
                </dd>
            </div>

            <div class="full-row">
                <dt>Ubicación</dt>
                <dd>
                    <?php
                    echo htmlspecialchars(
                        $siniestro['direccion']
                    );
                    ?>
                </dd>
            </div>

            <div class="full-row">
                <dt>Descripción</dt>
                <dd>
                    <?php
                    echo nl2br(
                        htmlspecialchars(
                            $siniestro['descripcion'] ?? ''
                        )
                    );
                    ?>
                </dd>
            </div>

        </dl>

    </section>


    <aside class="app-card">

        <h2 class="section-title">
            Historial de estatus
        </h2>

        <?php if (empty($historial)): ?>

            <p class="helper">
                Aún no hay movimientos registrados.
            </p>

        <?php else: ?>

            <ul class="timeline">

                <?php foreach ($historial as $h): ?>

                    <li>

                        <strong>
                            <?php
                            echo htmlspecialchars(
                                $h->estatus
                            );
                            ?>
                        </strong>

                        <small>
                            <?php
                            echo htmlspecialchars(
                                $h->fecha_actu
                            );
                            ?>
                        </small>

                    </li>

                <?php endforeach; ?>

            </ul>

        <?php endif; ?>

    </aside>

</div>


<!-- EVIDENCIAS -->
<?php if (Seguridad::esSupervisor()): ?>
<section class="app-card mt-4" id="asignacion">
    <h2 class="section-title">Asignar responsable</h2>
    <p class="helper">Solo los ajustadores activos pueden atender el expediente. Al reasignarlo, el ajustador anterior deja de tener acceso a este caso.</p>
    <?php if (!$ajustadores): ?><p class="alert-app error">No hay ajustadores activos disponibles.</p><?php endif; ?>
    <?php $casoAsignacion = $siniestro; $volverAsignacion = 'detalle'; require 'views/siniestros/formAsignacion.php'; ?>
</section>
<?php endif; ?>

<section class="app-card mt-4">

    <h2 class="section-title">
        Evidencias
    </h2>

    <?php if (!empty($siniestro['multimedia'])): ?>

        <div class="media-grid">

            <?php foreach ($siniestro['multimedia'] as $media): ?>

                <figure class="media-item">

                    <?php if ($media->tipo === 'Foto'): ?>

                        <img
                            src="<?php echo urlsite ?>?page=evidencia&amp;id=<?php echo (int) $media->id; ?>"
                            alt="Evidencia fotográfica del siniestro"
                            loading="lazy"
                        >

                    <?php elseif ($media->tipo === 'Video'): ?>

                        <video controls preload="metadata" playsinline>

                            <source
                                src="<?php echo urlsite ?>?page=evidencia&amp;id=<?php echo (int) $media->id; ?>"
                            >

                            Tu navegador no soporta video.

                        </video>

                    <?php endif; ?>

                </figure>

            <?php endforeach; ?>

        </div>

    <?php else: ?>

        <p class="empty-state">
            No hay fotos o videos registrados.
        </p>

    <?php endif; ?>

</section>


<!-- ACTUALIZAR ESTATUS: SOLO SUPERVISOR/ADMIN Y SOLO SI NO ESTÁ CERRADO -->
<?php require __DIR__ . '/chat/panel.php'; ?>

<?php if (
    isset($_SESSION['rol_slug']) &&
    in_array(
        $_SESSION['rol_slug'],
        ['supervisor', 'admin'],
        true
    ) &&
    !$estaCerrado
): ?>

    <section class="app-card mt-4">

        <h2 class="section-title">
            Actualizar estatus
        </h2>

        <form
            action="<?php echo urlsite ?>?page=actualizarEstatus"
            method="post"
        >
            <input type="hidden" name="csrf_token" value="<?php echo Seguridad::escapar(Seguridad::token()); ?>">

            <input
                type="hidden"
                name="id_siniestro"
                value="<?php echo (int) $siniestro['id']; ?>"
            >

            <div class="form-group-app">

                <label for="estatus_id">
                    Nuevo estatus
                </label>

                <select
                    id="estatus_id"
                    name="estatus_id"
                    required
                >

                    <option value="">
                        Selecciona un estatus
                    </option>

                    <?php foreach ($estatusDisponibles as $estado): ?>

                        <option
                            value="<?php echo (int) $estado->id; ?>"
                            <?php
                            echo (
                                $siniestro['Estatus'] ===
                                $estado->nombre
                            )
                                ? 'selected'
                                : '';
                            ?>
                        >
                            <?php
                            echo htmlspecialchars(
                                $estado->nombre
                            );
                            ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <button type="submit">
                Guardar cambio
            </button>

        </form>

    </section>

<?php endif; ?>


<!-- AVISO CUANDO YA ESTÁ CERRADO -->
<?php if (
    isset($_SESSION['rol_slug']) &&
    in_array(
        $_SESSION['rol_slug'],
        ['supervisor', 'admin'],
        true
    ) &&
    $estaCerrado
): ?>

    <section class="app-card mt-4">

        <h2 class="section-title">
            Siniestro cerrado
        </h2>

        <p class="helper">
            Este siniestro fue cerrado y su estatus ya no puede modificarse.
        </p>

    </section>

<?php endif; ?>


<!-- EVALUACIÓN -->
<?php if (
    isset($_SESSION['rol_slug']) &&
    in_array(
        $_SESSION['rol_slug'],
        ['ajustador', 'supervisor', 'admin'],
        true
    )
): ?>

    <section class="app-card mt-4">

        <h2 class="section-title">
            Evaluación del ajustador
        </h2>

        <form
            action="<?php echo urlsite ?>?page=guardarEvaluacion"
            method="post"
        >
            <input type="hidden" name="csrf_token" value="<?php echo Seguridad::escapar(Seguridad::token()); ?>">

            <input
                type="hidden"
                name="id_siniestro"
                value="<?php echo (int) $siniestro['id']; ?>"
            >

            <div class="form-group-app">

                <label for="diagnostico">
                    Diagnóstico preliminar
                </label>

                <textarea
                    id="diagnostico"
                    name="diagnostico"
                    required
                ><?php
                    echo htmlspecialchars(
                        $evaluacion['diagnostico'] ?? ''
                    );
                ?></textarea>

            </div>

            <button
                type="submit"
                name="btnEvaluacion"
            >
                <?php
                echo !empty($evaluacion)
                    ? 'Actualizar evaluación'
                    : 'Guardar evaluación';
                ?>
            </button>

        </form>

    </section>

<?php endif; ?>
