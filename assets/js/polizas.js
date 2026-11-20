/* Muestra solo los campos correspondientes a la aseguradora y el vehículo elegidos. */
(function () {
    'use strict';

    const aseguradora = document.getElementById('aseguradora_id');
    const aseguradoraNueva = document.getElementById('bloque-aseguradora-nueva');
    const nombreAseguradora = document.getElementById('aseguradora_nueva');
    const modoVehiculo = document.getElementById('vehiculo_modo');
    const vehiculoExistente = document.getElementById('bloque-vehiculo-existente');
    const vehiculoNuevo = document.getElementById('bloque-vehiculo-nuevo');
    const seleccionVehiculo = document.getElementById('vehiculo_id');

    if (!aseguradora || !modoVehiculo) return;

    function actualizarFormulario() {
        const esNueva = aseguradora.value === 'nueva';
        aseguradoraNueva.hidden = !esNueva;
        nombreAseguradora.required = esNueva;
        nombreAseguradora.disabled = !esNueva;

        const esExistente = modoVehiculo.value === 'existente';
        vehiculoExistente.hidden = !esExistente;
        seleccionVehiculo.required = esExistente;
        seleccionVehiculo.disabled = !esExistente;
        vehiculoNuevo.hidden = esExistente;
        vehiculoNuevo.querySelectorAll('input').forEach((campo) => {
            campo.disabled = esExistente;
            campo.required = !esExistente && ['marca', 'modelo', 'placas', 'serie'].includes(campo.name);
        });
    }

    aseguradora.addEventListener('change', actualizarFormulario);
    modoVehiculo.addEventListener('change', actualizarFormulario);
    actualizarFormulario();
})();
