    const inputEvidencias = document.getElementById('input-evidencias');
    const previewContainer = document.getElementById('preview-container');
    
    
    let archivosAcumulados = [];

    inputEvidencias.addEventListener('change', function(e) {
    
        const nuevosArchivos = Array.from(e.target.files);
        archivosAcumulados = archivosAcumulados.concat(nuevosArchivos);
        //actualiza el input para engañar al navegador y que tenga el listado completo de archivos seleccionados
        const dt = new DataTransfer();
        archivosAcumulados.forEach(file => dt.items.add(file));
        inputEvidencias.files = dt.files;

        actualizarPrevisualizacion();
    });

    function actualizarPrevisualizacion() {
        previewContainer.innerHTML = '';

        archivosAcumulados.forEach((file, index) => {
            // Creamos una URL temporal para que el navegador pueda leer el archivo
            const fileURL = URL.createObjectURL(file);
            
            
            const divMiniatura = document.createElement('div');
            divMiniatura.style.position = 'relative';
            divMiniatura.style.width = '120px';
            divMiniatura.style.height = '120px';
            divMiniatura.style.border = '1px solid #ccc';
            divMiniatura.style.borderRadius = '8px';
            divMiniatura.style.overflow = 'hidden';
            divMiniatura.style.backgroundColor = '#f8f9fa';


            if (file.type.startsWith('image/')) {
                const img = document.createElement('img');
                img.src = fileURL;
                img.style.width = '100%';
                img.style.height = '100%';
                img.style.objectFit = 'cover';
                divMiniatura.appendChild(img);
                
            } else if (file.type.startsWith('video/')) {
                const video = document.createElement('video');
                video.src = fileURL;
                video.style.width = '100%';
                video.style.height = '100%';
                video.style.objectFit = 'cover';
                video.muted = true; 
                video.controls = true; 
                divMiniatura.appendChild(video);
            }

            // Opcional: Agregar un botón de "X" para eliminar un archivo si se equivocó
            const btnEliminar = document.createElement('button');
            btnEliminar.innerHTML = 'X';
            btnEliminar.style.position = 'absolute';
            btnEliminar.style.top = '5px';
            btnEliminar.style.right = '5px';
            btnEliminar.style.background = 'red';
            btnEliminar.style.color = 'white';
            btnEliminar.style.border = 'none';
            btnEliminar.style.borderRadius = '50%';
            btnEliminar.style.cursor = 'pointer';
            
            btnEliminar.onclick = function(e) {
                e.preventDefault(); // Evita que se envíe el formulario
                eliminarArchivo(index);
            };

            divMiniatura.appendChild(btnEliminar);
            previewContainer.appendChild(divMiniatura);
        });
    }

    function eliminarArchivo(index) {
        // Quitamos el archivo del arreglo global
        archivosAcumulados.splice(index, 1);
        
        
        const dt = new DataTransfer();
        archivosAcumulados.forEach(file => dt.items.add(file));
        inputEvidencias.files = dt.files;
        
        // Volvemos a dibujar
        actualizarPrevisualizacion();
    }