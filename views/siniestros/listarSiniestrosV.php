<?php /* Buscador con filtros enviados al procedimiento sp_buscar_siniestros. */ ?>
<div class="page-heading">
    <h1><?php echo (($_SESSION['rol_slug'] ?? '')==='ajustador') ? 'Mis siniestros asignados' : 'Siniestros'; ?></h1>
    <p><?php echo !empty($mostrarFiltros) ? 'Busca por folio, póliza, asegurado, placas, modelo, ajustador o dirección. Hasta 500 resultados.' : 'Consulta el estado y los detalles de cada reporte.'; ?></p>
</div>
<?php if (!empty($mostrarFiltros)): ?>
<section class="app-card busqueda-siniestros">
    <form method="get" action="<?php echo urlsite; ?>" class="buscador-siniestros-form" role="search">
        <input type="hidden" name="page" value="misSiniestros">
        <div class="buscador-campo buscador-texto">
            <label for="sin-busqueda">Buscar siniestros</label>
            <input type="search" id="sin-busqueda" name="q" maxlength="150" placeholder="Folio, póliza, cliente, placas..." value="<?php echo Seguridad::escapar($filtros['q']); ?>">
        </div>
        <div class="buscador-campo">
            <label for="sin-estatus">Estatus</label>
            <select id="sin-estatus" name="estatus">
                <option value="0">Todos los estatus</option>
                <option value="-1" <?php echo (int)$filtros['estatus']===-1?'selected':''; ?>>Sin estatus</option>
                <?php foreach($estatusDisponibles as $estado): ?>
                <option value="<?php echo (int)$estado->id; ?>" <?php echo (int)$filtros['estatus']===(int)$estado->id?'selected':''; ?>><?php echo Seguridad::escapar($estado->nombre); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php if (Seguridad::esSupervisor()): ?>
        <div class="buscador-campo">
            <label for="sin-ajustador">Ajustador</label>
            <select id="sin-ajustador" name="ajustador">
                <option value="0">Todos los ajustadores</option>
                <option value="-1" <?php echo (int)$filtros['ajustador']===-1?'selected':''; ?>>Sin asignar</option>
                <?php foreach($ajustadores as $aj): ?>
                <option value="<?php echo (int)$aj['id']; ?>" <?php echo (int)$filtros['ajustador']===(int)$aj['id']?'selected':''; ?>><?php echo Seguridad::escapar($aj['nombre'].((int)$aj['activo']===0?' (inactivo)':'')); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        <div class="buscador-campo"><label for="sin-desde">Desde</label><input type="date" id="sin-desde" name="desde" value="<?php echo Seguridad::escapar($filtros['desde']??''); ?>"></div>
        <div class="buscador-campo"><label for="sin-hasta">Hasta</label><input type="date" id="sin-hasta" name="hasta" value="<?php echo Seguridad::escapar($filtros['hasta']??''); ?>"></div>
        <div class="buscador-acciones"><button class="btn" type="submit">Buscar</button><a class="btn btn-outline-app" href="<?php echo urlsite; ?>?page=misSiniestros">Limpiar filtros</a></div>
    </form>
</section>
<p class="resultados-contador" aria-live="polite"><?php echo count($misSiniestros); ?> siniestro(s) encontrado(s)<?php echo count($misSiniestros)===500?' · Se muestran los 500 más recientes':''; ?></p>
<?php endif; ?>
<section class="cards-grid">
<?php if (empty($misSiniestros)): ?>
    <div class="empty-state"><h2>Sin resultados</h2><p><?php echo !empty($mostrarFiltros) ? 'Prueba con otros filtros o limpia la búsqueda.' : 'No hay siniestros para mostrar.'; ?></p></div>
<?php else: foreach($misSiniestros as $siniestro): ?>
    <article class="claim-card">
        <div class="info">
            <span class="card-number">Siniestro #<?php echo (int)$siniestro->id; ?></span>
            <strong>Póliza:</strong> <?php echo Seguridad::escapar($siniestro->Poliza); ?><br>
            <?php if (($_SESSION['rol_slug'] ?? '')!=='asegurado'): ?>
                <strong>Asegurado:</strong> <?php echo Seguridad::escapar($siniestro->nombreCliente); ?><br>
                <strong>Ajustador:</strong> <?php echo Seguridad::escapar($siniestro->Ajustador); ?><br>
            <?php endif; ?>
            <strong>Vehículo:</strong> <?php echo Seguridad::escapar($siniestro->Marca.' '.$siniestro->Modelo); ?><br>
            <strong>Fecha:</strong> <?php echo Seguridad::escapar($siniestro->fecha); ?>
        </div>
        <div class="acciones"><span class="status-badge"><?php echo Seguridad::escapar($siniestro->Estatus); ?></span>
        <a class="vermas" href="<?php echo urlsite; ?>?page=detalle&amp;id=<?php echo (int)$siniestro->id; ?>">Ver detalles</a></div>
    </article>
<?php endforeach; endif; ?>
</section>
