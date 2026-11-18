/** Chat por sondeo corto: sin servicios externos ni WebSockets en XAMPP. */
(function () {
    'use strict';
    const panel = document.getElementById('chat');
    if (!panel || !window.fetch || !window.AbortController) return;
    const form = panel.querySelector('.chat-form');
    if (!form) return;
    const log = panel.querySelector('.chat-log');
    const input = form.querySelector('textarea');
    const submit = form.querySelector('button[type="submit"]');
    const older = panel.querySelector('[data-chat-older]');
    const empty = panel.querySelector('.chat-empty');
    const notice = panel.querySelector('.chat-error');
    const status = panel.querySelector('.chat-status');
    const counter = panel.querySelector('#chat-counter');
    const newButton = panel.querySelector('.chat-new');
    const id = Number(panel.dataset.case);
    const user = Number(panel.dataset.user);
    const known = new Set(Array.from(log.querySelectorAll('[data-id]'), node => Number(node.dataset.id)));
    let latest = Number(panel.dataset.latest);
    let first = Number(panel.dataset.first);
    let pendingRead = null;
    let timer = null;
    let sending = false;
    let loadingOlder = false;
    let stopped = false;

    function bottom() {
        log.scrollTop = log.scrollHeight;
        newButton.hidden = true;
    }

    function atBottom() {
        return log.scrollHeight - log.scrollTop - log.clientHeight < 80;
    }

    function showError(error, source) {
        notice.textContent = error.message;
        notice.dataset.source = source;
        notice.hidden = false;
        if ([401, 403, 404].includes(error.status)) {
            stopped = true;
            clearTimeout(timer);
            input.disabled = true;
            input.value = '';
            submit.disabled = true;
            older.hidden = true;
            newButton.hidden = true;
            log.querySelectorAll('[data-id]').forEach(node => node.remove());
            panel.querySelectorAll('[data-participant]').forEach(node => { node.textContent = '—'; });
            empty.textContent = 'Chat no disponible. Recarga el expediente o inicia sesión nuevamente.';
            empty.hidden = false;
            status.textContent = 'El acceso al chat se ha cerrado.';
        }
    }

    function clearError(source) {
        if (!source || notice.dataset.source === source) {
            notice.hidden = true;
            notice.textContent = '';
        }
    }

    async function request(url, options = {}) {
        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 15000);
        try {
            const response = await fetch(url, {
                ...options, credentials: 'same-origin', cache: 'no-store',
                headers: { Accept: 'application/json' }, signal: controller.signal
            });
            let data;
            try { data = await response.json(); }
            catch (_) { throw new Error('El servidor no respondió correctamente. Recarga el expediente.'); }
            if (!response.ok || data.ok !== true) {
                const error = new Error(data.error || 'No se pudo completar la operación.');
                error.status = response.status;
                throw error;
            }
            // Si otra pestaña cambió de cuenta, no mezclar conversaciones o autores.
            if (data.usuario_id !== undefined && Number(data.usuario_id) !== user) {
                const error = new Error('La sesión cambió de cuenta. Recarga el expediente.');
                error.status = 401;
                throw error;
            }
            return data;
        } finally {
            clearTimeout(timeout);
        }
    }

    function url(cursor, value) {
        const target = new URL(panel.dataset.url, window.location.href);
        target.searchParams.set('id', String(id));
        if (value > 0) target.searchParams.set(cursor, String(value));
        return target;
    }

    function element(tag, className, text) {
        const node = document.createElement(tag);
        node.className = className;
        node.textContent = text;
        return node;
    }

    function messageNode(message) {
        const own = Number(message.usuario_id) === user;
        const article = document.createElement('article');
        article.className = 'chat-message' + (own ? ' is-mine' : '');
        article.dataset.id = String(message.id);
        const meta = document.createElement('div');
        meta.className = 'chat-message-meta';
        meta.append(element('strong', '', message.autor), element('span', 'chat-role', message.rol_nombre));
        if (own) meta.append(element('span', 'helper', 'Tú'));
        // textContent evita ejecutar HTML, incluso si el mensaje contiene etiquetas.
        article.append(meta, element('p', 'chat-message-text', message.mensaje),
            element('time', 'chat-message-time', message.fecha));
        return article;
    }

    function insert(messages, prepend) {
        if (stopped) return 0;
        const fragment = document.createDocumentFragment();
        let count = 0;
        messages.forEach(message => {
            const messageId = Number(message.id);
            if (known.has(messageId)) return;
            known.add(messageId);
            first = first ? Math.min(first, messageId) : messageId;
            fragment.append(messageNode(message));
            count++;
        });
        if (prepend) log.insertBefore(fragment, log.firstChild);
        else log.append(fragment);
        empty.hidden = known.size > 0;
        older.href = 'index.php?page=detalle&id=' + id + '&chat_antes=' + first + '#chat';
        return count;
    }

    function participants(data) {
        if (stopped) return;
        panel.querySelector('[data-participant="asegurado"]').textContent = data.asegurado;
        panel.querySelector('[data-participant="ajustador"]').textContent = data.ajustador || 'Sin asignar';
    }

    function schedule(delay = 5000) {
        clearTimeout(timer);
        if (!stopped && !document.hidden) timer = setTimeout(() => refresh(), delay);
    }

    function refresh(forceBottom = false) {
        if (stopped || document.hidden) return Promise.resolve();
        if (pendingRead) return pendingRead;
        let more = false;
        pendingRead = (async () => {
            try {
                const initial = latest === 0;
                const data = await request(url('despues', latest));
                if (stopped) return;
                const stick = forceBottom || atBottom();
                const count = insert(data.mensajes, false);
                data.mensajes.forEach(message => { latest = Math.max(latest, Number(message.id)); });
                if (initial) older.hidden = !data.hay_anteriores;
                participants(data.participantes);
                more = data.hay_mas;
                if (stick) bottom();
                else if (count) newButton.hidden = false;
                clearError('read');
                if (!sending) status.textContent = 'Actualización automática cada 5 segundos.';
            } catch (error) {
                if (!error.status) error.message = 'Sin conexión con el chat. Se volverá a intentar; tu borrador se conserva.';
                showError(error, 'read');
            } finally {
                pendingRead = null;
                schedule(more ? 150 : 5000);
            }
        })();
        return pendingRead;
    }

    older.addEventListener('click', async event => {
        event.preventDefault();
        if (loadingOlder || stopped || !first) return;
        loadingOlder = true;
        older.setAttribute('aria-disabled', 'true');
        try {
            const data = await request(url('antes', first));
            if (stopped) return;
            const previousHeight = log.scrollHeight;
            const previousTop = log.scrollTop;
            insert(data.mensajes, true);
            log.scrollTop = previousTop + log.scrollHeight - previousHeight;
            older.hidden = !data.hay_anteriores;
            participants(data.participantes);
            clearError('history');
        } catch (error) {
            if (!error.status) error.message = 'No se pudo cargar el historial. Inténtalo de nuevo.';
            showError(error, 'history');
        } finally {
            loadingOlder = false;
            older.removeAttribute('aria-disabled');
        }
    });

    function updateCounter() {
        const length = Array.from(input.value).length;
        counter.textContent = length + ' / 2000 caracteres';
        input.setCustomValidity(length > 2000 ? 'El mensaje no puede superar los 2000 caracteres.' : '');
    }

    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (sending || stopped) return;
        updateCounter();
        if (!input.value.trim() || Array.from(input.value).length > 2000) {
            showError(new Error('Escribe un mensaje de entre 1 y 2000 caracteres.'), 'send');
            return;
        }
        const body = new FormData(form);
        sending = true;
        input.readOnly = true;
        submit.disabled = true;
        submit.textContent = 'Enviando…';
        clearError();
        status.textContent = 'Enviando mensaje…';
        try {
            await request(form.action, { method: 'POST', body });
            input.value = '';
            updateCounter();
            // No avanzar latest con el ID enviado: podría saltarse mensajes de otros
            // participantes que llegaron mientras se estaba escribiendo/enviando.
            if (pendingRead) await pendingRead;
            await refresh(true);
            if (!stopped) status.textContent = 'Mensaje enviado.';
        } catch (error) {
            if (!error.status || error.status >= 500) {
                error.message = 'No se pudo confirmar el envío. Revisa si el mensaje apareció antes de reenviarlo; tu texto se conserva.';
            }
            showError(error, 'send');
            if (!stopped) status.textContent = 'El envío no fue confirmado.';
        } finally {
            sending = false;
            input.readOnly = false;
            submit.disabled = stopped;
            submit.textContent = 'Enviar mensaje';
            if (!stopped) input.focus({ preventScroll: true });
        }
    });

    input.addEventListener('input', updateCounter);
    input.addEventListener('keydown', event => {
        if (event.key === 'Enter' && (event.ctrlKey || event.metaKey)) {
            event.preventDefault();
            form.requestSubmit();
        }
    });
    newButton.addEventListener('click', bottom);
    log.addEventListener('scroll', () => { if (atBottom()) newButton.hidden = true; });
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) clearTimeout(timer);
        else refresh();
    });
    window.addEventListener('online', () => refresh());
    window.addEventListener('pageshow', event => { if (event.persisted) window.location.reload(); });
    updateCounter();
    bottom();
    refresh();
})();
