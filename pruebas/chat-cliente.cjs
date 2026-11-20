/* node pruebas/chat-cliente.cjs
 * Pruebas del JS real con DOM/respuestas simulados. No reemplazan pruebas de PHP/SQL.
 */
'use strict';
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.join(__dirname, '../assets/js/chat.js'), 'utf8');

class Element {
    constructor(tag = 'div', className = '') {
        this.tag = tag; this.className = className; this.children = []; this.dataset = {};
        this.attributes = {}; this.events = {}; this.textContent = ''; this.hidden = false;
        this.value = ''; this.scrollTop = 0; this.clientHeight = 240;
    }
    get firstChild() { return this.children[0] || null; }
    get scrollHeight() { return this.children.length * 90; }
    append(...items) { items.forEach(item => this.insertBefore(item, null)); }
    insertBefore(item, target) {
        if (item.tag === 'fragment') { [...item.children].forEach(child => this.insertBefore(child, target)); return; }
        item.remove();
        const index = target ? this.children.indexOf(target) : this.children.length;
        this.children.splice(index, 0, item); item.parent = this;
    }
    remove() {
        if (this.parent) this.parent.children.splice(this.parent.children.indexOf(this), 1);
        this.parent = null;
    }
    matches(selector) {
        if (selector.startsWith('.')) return this.className.split(' ').includes(selector.slice(1));
        if (selector.startsWith('#')) return this.id === selector.slice(1);
        const attr = selector.match(/^([a-z]+)?\[([\w-]+)(?:="([^"]*)")?\]$/);
        if (attr) {
            const key = attr[2].replace(/^data-/, '').replace(/-([a-z])/g, (_, letter) => letter.toUpperCase());
            const value = attr[2].startsWith('data-') ? this.dataset[key] : this.attributes[attr[2]];
            return (!attr[1] || this.tag === attr[1]) && value !== undefined && (attr[3] === undefined || value === attr[3]);
        }
        return this.tag === selector;
    }
    querySelectorAll(selector) {
        return this.children.flatMap(child => [...(child.matches(selector) ? [child] : []), ...child.querySelectorAll(selector)]);
    }
    querySelector(selector) { return this.querySelectorAll(selector)[0] || null; }
    setAttribute(name, value) { this.attributes[name] = value; }
    removeAttribute(name) { delete this.attributes[name]; }
    addEventListener(name, callback) { (this.events[name] ||= []).push(callback); }
    async emit(name, detail = {}) {
        for (const callback of this.events[name] || []) await callback({ preventDefault() {}, ...detail });
    }
    setCustomValidity(text) { this.validationMessage = text; }
    requestSubmit() { return this.emit('submit'); }
    focus() {}
}

const tick = async () => { for (let i = 0; i < 8; i++) await new Promise(resolve => setImmediate(resolve)); };
const message = (id, user = 10, text = 'Mensaje ' + id) => ({ id, usuario_id: user,
    autor: user === 10 ? 'Asegurado' : 'Ajustador', rol_nombre: user === 10 ? 'Asegurado' : 'Ajustador',
    mensaje: text, fecha: '2026-01-01 10:00:00' });
const page = (messages = [], extra = {}) => ({ ok: true, usuario_id: 10, mensajes: messages,
    participantes: { asegurado: 'Cliente', ajustador: 'Responsable' }, hay_anteriores: false, hay_mas: false, ...extra });

function fixture(initial = []) {
    const panel = new Element('section'); panel.id = 'chat';
    panel.dataset = { case: '3', user: '10', latest: String(initial.at(-1)?.id || 0),
        first: String(initial[0]?.id || 0), url: 'index.php?page=chatMensajes' };
    const log = new Element('div', 'chat-log');
    const empty = new Element('p', 'chat-empty'); log.append(empty);
    initial.forEach(item => { const node = new Element('article', 'chat-message'); node.dataset.id = String(item.id); log.append(node); });
    const older = new Element('a'); older.dataset.chatOlder = '';
    const notice = new Element('p', 'chat-error'); notice.hidden = true;
    const status = new Element('p', 'chat-status');
    const counter = new Element('small'); counter.id = 'chat-counter';
    const newButton = new Element('button', 'chat-new'); newButton.hidden = true;
    const form = new Element('form', 'chat-form'); form.action = 'http://localhost/RegistroSiniestros/index.php?page=chatEnviar';
    const input = new Element('textarea'); input.attributes.name = 'mensaje';
    const token = new Element('input'); token.attributes.name = 'csrf_token'; token.value = 'token-prueba';
    const caseInput = new Element('input'); caseInput.attributes.name = 'id_siniestro'; caseInput.value = '3';
    const button = new Element('button'); button.attributes.type = 'submit';
    form.append(input, token, caseInput, button);
    panel.append(log, older, notice, status, counter, newButton, form);
    ['asegurado', 'ajustador'].forEach(role => { const item = new Element('span'); item.dataset.participant = role; panel.append(item); });
    const document = new Element('document'); document.append(panel); document.hidden = false;
    document.getElementById = id => id === 'chat' ? panel : null;
    document.createElement = tag => new Element(tag);
    document.createDocumentFragment = () => new Element('fragment');
    const window = new Element('window'); window.location = { href: 'http://localhost/RegistroSiniestros/index.php?page=detalle&id=3', reload() {} };
    const requests = [];
    const fetch = (url, options) => new Promise((resolve, reject) => requests.push({ url: String(url), options, resolve, reject }));
    const timers = new Map(); let timerId = 0;
    class FormData {
        constructor(node) { this.fields = new Map(node.children.filter(child => child.attributes.name).map(child => [child.attributes.name, child.value])); }
        get(key) { return this.fields.get(key); }
    }
    window.fetch = fetch; window.AbortController = AbortController;
    vm.runInNewContext(source, { window, document, fetch, FormData, URL, AbortController,
        setTimeout: (fn, delay) => { timers.set(++timerId, { fn, delay }); return timerId; },
        clearTimeout: id => timers.delete(id), console });
    const respond = async (data, statusCode = 200) => {
        const request = requests.shift(); assert.ok(request, 'Debe existir una petición pendiente');
        request.resolve({ ok: statusCode >= 200 && statusCode < 300, status: statusCode, json: async () => data });
        await tick(); return request;
    };
    return { panel, log, empty, older, notice, status, counter, newButton, form, input, button,
        document, window, requests, timers, respond, ids: () => log.querySelectorAll('[data-id]').map(node => Number(node.dataset.id)) };
}

(async () => {
    let checks = 0;
    const ok = name => { checks++; console.log('OK: ' + name); };
    const f = fixture();
    assert.match(f.requests[0].url, /id=3/);
    assert.equal(f.requests[0].options.credentials, 'same-origin');
    assert.equal(f.requests[0].options.cache, 'no-store');
    await f.respond(page([message(1), message(2, 20, '<img src=x onerror=alert(1)>')]));
    assert.deepEqual(f.ids(), [1, 2]); assert.equal(f.empty.hidden, true);
    assert.equal(f.log.querySelectorAll('.chat-message-text')[1].textContent, '<img src=x onerror=alert(1)>');
    assert.equal(f.log.querySelectorAll('img').length, 0);
    ok('Carga inicial por caso, sin caché, renderizado de HTML como texto');

    const poll = f.window.emit('online');
    assert.match(f.requests[0].url, /despues=2/);
    await f.respond(page([message(2, 20), message(3, 20)]));
    await poll;
    assert.deepEqual(f.ids(), [1, 2, 3]);
    ok('Sondeo incremental y eliminación de duplicados');

    f.input.value = 'Consulta nueva';
    const send = f.form.emit('submit');
    assert.equal(f.button.disabled, true);
    assert.equal(f.requests[0].options.method, 'POST');
    assert.equal(f.requests[0].options.body.get('csrf_token'), 'token-prueba');
    assert.equal(f.requests[0].options.body.get('id_siniestro'), '3');
    await f.respond({ ok: true, id: 5 }, 201);
    assert.match(f.requests[0].url, /despues=3/);
    await f.respond(page([message(4, 20), message(5)]));
    await send;
    assert.deepEqual(f.ids(), [1, 2, 3, 4, 5]); assert.equal(f.input.value, '');
    assert.equal(f.button.disabled, false);
    ok('Envío con CSRF sin saltarse mensajes intercalados de otros participantes');

    f.input.value = '  ';
    await f.form.emit('submit'); assert.equal(f.requests.length, 0); assert.equal(f.notice.hidden, false);
    f.input.value = '🚗'.repeat(2001); await f.input.emit('input');
    assert.match(f.input.validationMessage, /2000/);
    await f.form.emit('submit'); assert.equal(f.requests.length, 0);
    ok('Rechazo local de vacío y longitud Unicode excesiva');

    f.input.value = 'Conservar borrador';
    const failedSend = f.form.emit('submit');
    await f.respond({ ok: false, error: 'Mensaje inválido' }, 422); await failedSend;
    assert.equal(f.input.value, 'Conservar borrador'); assert.match(f.notice.textContent, /inválido/);
    const afterError = f.window.emit('online'); await f.respond(page([])); await afterError;
    assert.equal(f.notice.hidden, false);
    ok('Error de envío visible y borrador conservado tras nueva lectura');

    const h = fixture([message(51), message(52)]);
    await h.respond(page([]));
    const history = h.older.emit('click');
    assert.match(h.requests[0].url, /antes=51/);
    await h.respond(page([message(49), message(50)], { hay_anteriores: true })); await history;
    assert.deepEqual(h.ids(), [49, 50, 51, 52]); assert.equal(h.older.hidden, false);
    assert.match(h.older.href, /chat_antes=49/);
    ok('Historial se antepone en orden sin modificar el cursor de mensajes nuevos');

    const deniedPoll = h.window.emit('online'); assert.match(h.requests[0].url, /despues=52/);
    await h.respond({ ok: false, error: 'No tienes acceso a este chat.' }, 403);
    await deniedPoll;
    assert.deepEqual(h.ids(), []); assert.equal(h.input.disabled, true); assert.equal(h.button.disabled, true);
    assert.equal(h.older.hidden, true); assert.equal(h.empty.hidden, false);
    await h.window.emit('online'); assert.equal(h.requests.length, 0);
    ok('Reasignación/403 borra historial visible, bloquea envío y detiene sondeo');

    const n = fixture();
    n.input.value = 'Texto sin enviar'; n.requests.shift().reject(new Error('offline')); await tick();
    assert.equal(n.input.value, 'Texto sin enviar'); assert.equal(n.notice.hidden, false);
    assert.ok([...n.timers.values()].some(item => item.delay === 5000));
    n.document.hidden = true; await n.document.emit('visibilitychange'); assert.equal(n.timers.size, 0);
    n.document.hidden = false; await n.document.emit('visibilitychange');
    await n.respond(page([])); assert.equal(n.notice.hidden, true);
    ok('Fallo de red reintenta lectura; pestaña oculta pausa y visible retoma');

    const u = fixture([message(1)]);
    await u.respond(page([], { usuario_id: 99 }));
    assert.deepEqual(u.ids(), []); assert.equal(u.button.disabled, true);
    ok('Cambio de cuenta en otra pestaña cierra la conversación anterior');

    const d = fixture(); await d.respond(page([]));
    d.input.value = 'Un solo envío';
    const pending = d.form.emit('submit'); await d.form.emit('submit');
    assert.equal(d.requests.length, 1);
    d.requests.shift().reject(new Error('respuesta perdida')); await tick(); await pending;
    assert.equal(d.input.value, 'Un solo envío'); assert.match(d.notice.textContent, /antes de reenviarlo/);
    assert.equal(d.requests.length, 0);
    ok('Doble clic no duplica POST y una respuesta perdida no reenvía automáticamente');

    console.log('\n' + checks + ' escenarios JavaScript completados. PHP/SQL requieren XAMPP.');
})().catch(error => { console.error(error); process.exitCode = 1; });
