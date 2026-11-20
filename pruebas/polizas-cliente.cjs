/* Prueba sin dependencias de la visibilidad y validación del formulario de pólizas. */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

function elemento(valor = '') {
    return {
        value: valor, required: false, disabled: false, hidden: false,
        listeners: {},
        addEventListener(evento, callback) { this.listeners[evento] = callback; },
        change() { this.listeners.change(); }
    };
}

const elementos = {
    aseguradora_id: elemento('nueva'),
    'bloque-aseguradora-nueva': elemento(),
    aseguradora_nueva: elemento(),
    vehiculo_modo: elemento('nuevo'),
    'bloque-vehiculo-existente': elemento(),
    'bloque-vehiculo-nuevo': elemento(),
    vehiculo_id: elemento()
};
const camposVehiculo = ['marca', 'modelo', 'color', 'placas', 'serie'].map((nombre) => ({
    ...elemento(), name: nombre
}));
elementos['bloque-vehiculo-nuevo'].querySelectorAll = () => camposVehiculo;

const codigo = fs.readFileSync(path.join(__dirname, '../assets/js/polizas.js'), 'utf8');
vm.runInNewContext(codigo, {
    document: { getElementById: (id) => elementos[id] ?? null }
});

assert.equal(elementos['bloque-aseguradora-nueva'].hidden, false);
assert.equal(elementos.aseguradora_nueva.required, true);
assert.equal(elementos['bloque-vehiculo-nuevo'].hidden, false);
assert.equal(elementos['bloque-vehiculo-existente'].hidden, true);
assert.equal(camposVehiculo.find((campo) => campo.name === 'marca').required, true);
assert.equal(camposVehiculo.find((campo) => campo.name === 'color').required, false);

elementos.aseguradora_id.value = '3';
elementos.aseguradora_id.change();
assert.equal(elementos['bloque-aseguradora-nueva'].hidden, true);
assert.equal(elementos.aseguradora_nueva.disabled, true);

elementos.vehiculo_modo.value = 'existente';
elementos.vehiculo_modo.change();
assert.equal(elementos['bloque-vehiculo-nuevo'].hidden, true);
assert.equal(elementos.vehiculo_id.required, true);
assert.equal(elementos.vehiculo_id.disabled, false);
assert.ok(camposVehiculo.every((campo) => campo.disabled && !campo.required));

console.log('OK: campos de póliza y vehículo activados según la selección.');
