/* Acumula evidencias sin repetir archivos y evita peticiones demasiado grandes. */
(function () {
    'use strict';
    const input = document.getElementById('input-evidencias');
    const contenedor = document.getElementById('preview-container');
    const aviso = document.getElementById('evidencias-error');
    if (!input || !contenedor) return;

    const MB = 1024 * 1024;
    const MAX_ARCHIVOS = 10;
    const MAX_TOTAL = 110 * MB;
    const MAX_VIDEO = 80 * MB;
    const MAX_FOTO = 10 * MB;
    const permitidos = new Set(['image/jpeg', 'image/png', 'image/webp', 'image/gif',
        'video/mp4', 'video/webm', 'video/quicktime', 'video/ogg']);
    let archivos = [];
    let enlacesTemporales = [];

    function mostrarError(mensaje) {
        if (!aviso) return;
        aviso.textContent = mensaje;
        aviso.hidden = !mensaje;
    }

    function actualizarInput() {
        const transferencia = new DataTransfer();
        archivos.forEach(archivo => transferencia.items.add(archivo));
        input.files = transferencia.files;
    }

    function actualizarVista() {
        enlacesTemporales.forEach(url => URL.revokeObjectURL(url));
        enlacesTemporales = [];
        contenedor.replaceChildren();
        archivos.forEach((archivo, indice) => {
            const enlace = URL.createObjectURL(archivo);
            enlacesTemporales.push(enlace);
            const tarjeta = document.createElement('div');
            tarjeta.style.cssText = 'position:relative;width:120px;height:140px;display:inline-flex;flex-direction:column;margin:8px;border:1px solid #ccc;border-radius:8px;overflow:hidden;background:#f8f9fa;';
            const esVideo = archivo.type.startsWith('video/');
            const vista = document.createElement(esVideo ? 'video' : 'img');
            vista.src = enlace;
            vista.style.cssText = 'width:100%;height:110px;object-fit:cover;';
            if (esVideo) { vista.muted = true; vista.controls = true; vista.preload = 'metadata'; }
            else vista.alt = 'Vista previa de evidencia';
            const etiqueta = document.createElement('small');
            etiqueta.textContent = archivo.name;
            etiqueta.title = archivo.name;
            etiqueta.style.cssText = 'padding:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;';
            const borrar = document.createElement('button');
            borrar.type = 'button';
            borrar.textContent = '×';
            borrar.setAttribute('aria-label', 'Quitar ' + archivo.name);
            borrar.style.cssText = 'position:absolute;top:4px;right:4px;background:#ac2222;color:#fff;border:0;border-radius:50%;width:26px;height:26px;cursor:pointer;';
            borrar.addEventListener('click', () => {
                archivos.splice(indice, 1);
                actualizarInput();
                actualizarVista();
                mostrarError('');
            });
            tarjeta.append(vista, etiqueta, borrar);
            contenedor.appendChild(tarjeta);
        });
    }

    input.addEventListener('change', (event) => {
        const nuevos = Array.from(event.target.files);
        let error = '';
        for (const archivo of nuevos) {
            if (archivos.some(actual => actual.name === archivo.name && actual.size === archivo.size && actual.lastModified === archivo.lastModified)) continue;
            if (!permitidos.has(archivo.type)) { error = 'Formato no admitido. Usa JPG, PNG, WEBP, GIF, MP4, WEBM, MOV u OGV.'; break; }
            if (!archivo.size) { error = 'No puedes adjuntar archivos vacíos.'; break; }
            if (archivo.size > (archivo.type.startsWith('video/') ? MAX_VIDEO : MAX_FOTO)) {
                error = 'Una foto supera 10 MB o un video supera 80 MB.'; break;
            }
            if (archivos.length >= MAX_ARCHIVOS) { error = 'Puedes adjuntar como máximo 10 archivos.'; break; }
            if (archivos.reduce((suma, actual) => suma + actual.size, 0) + archivo.size > MAX_TOTAL) {
                error = 'El conjunto de evidencias supera 110 MB.'; break;
            }
            archivos.push(archivo);
        }
        actualizarInput();
        actualizarVista();
        mostrarError(error);
    });

    window.addEventListener('pagehide', () => enlacesTemporales.forEach(url => URL.revokeObjectURL(url)));
})();
