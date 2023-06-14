(function () {
    'use strict';

    const config = {
        batch: new Date().getTime(),

        callbacks: {
            success: {},
            failure: {},
        },
        ctx: {},
        queue: {},
        timeout_id: null,
        timeout_delay: 100,
        timestamps: {},
    };

        config.callbacks.success[config.batch] = [];
        config.callbacks.failure[config.batch] = [];
        config.ctx[config.batch] = [];
        config.queue[config.batch] = [];

    /**
     * Generise FormData od objekata (ili nizova)
     *
     * @param   {FormData}  formData        FormData u koji se ubacuju
     * @param   {*}         data            Podaci koji se ubacuju
     * @param   {String}    parentKey       Kljuc u koji se podaci ubacuju
     */
    function buildFormData(formData, data, parentKey) {
    if (
        data &&
        typeof data === 'object' &&
        !(data instanceof Date) &&
        !(data instanceof File)
    ) {
        Object.keys(data).forEach((key) => {
            buildFormData(
            formData,
            data[key],
            parentKey ? parentKey + '[' + key + ']' : key
            );
        });
    } else {
        const value = data == null ? '' : data;
        formData.append(parentKey, value);
    }
}

let callCallbacks = function (data, batch, which, callback_no) {
    if (callback_no !== undefined) {
        const callbacks = config.callbacks[which][batch][callback_no];
        const ctx = config.ctx[batch][callback_no];
        if (data !== undefined && data !== undefined && data.ok) {
            const is_latest = data.timestamp === config.timestamps[data.key];
            callbacks(data.data, ctx, is_latest);
        } else if (data.ok === false) {
            const is_latest = data.timestamp === config.timestamps[data.key];
            callbacks(data.error, ctx, is_latest);
        }
    }
};

let callCallbacksSuccess = function (data, batch, callback_no) {
    callCallbacks(data, batch, 'success', callback_no);
};

let callCallbacksFailure = function (data, batch, callback_no) {
    const detail = {
        error_message: data.error_message,
    }
    const event = new CustomEvent('Task.Error', {
        detail,
    });
    document.dispatchEvent(event);
    callCallbacks(data, batch, 'failure', callback_no);
};

let makeRequestRegular = function () {
    let batch = config.batch;
    if (config.queue[batch].length > 0) {
        const csrf_field = document.querySelector('#csrf-token');
        const csrf_token = csrf_field ? csrf_field.content : '';

        const request = new XMLHttpRequest();
        request.open('POST', '/laravel-tasks/public/ajax/data', true);
        request.setRequestHeader(
            'Content-Type',
            'application/json; charset=UTF-8'
        );
        request.setRequestHeader('X-CSRF-TOKEN', csrf_token);
        request.onreadystatechange = function () {
            if (request.readyState === 4) {
                if (request.status === 200) {
                    const response = JSON.parse(request.responseText);
                    const batch = response.batch;
                    const response_data = response.data;
                    for (let i = 0, l = response_data.length; i < l; i++) {
                        if (response_data[i].ok) {
                            callCallbacksSuccess(response_data[i], batch, i);
                        } else {
                            callCallbacksFailure(response_data[i], batch, i);
                        }
                    }
                } else {
                    try {
                        callCallbacksFailure(JSON.parse(request.responseText));
                    } catch (e) {
                        callCallbacksFailure();
                    }
                }

                config.callbacks.success[batch] = [];
                config.callbacks.failure[batch] = [];
                config.ctx[batch] = [];
                config.queue[batch] = [];
            }
        };

        request.send(
            JSON.stringify({
                queue: config.queue[batch],
                batch: config.batch,
            })
        );
        config.batch = new Date().getTime();
        config.callbacks.success[config.batch] = [];
        config.callbacks.failure[config.batch] = [];
        config.ctx[config.batch] = [];
        config.queue[config.batch] = [];
    }
};

let makeRequestRaw = function (data, callback, ctx) {
    const request = new XMLHttpRequest();
    const csrf_field = document.querySelector('#csrf-token');
    const csrf_token = csrf_field ? csrf_field.content : '';
    request.open('POST', '/laravel-tasks/public/ajax/data_raw');
    request.setRequestHeader('X-CSRF-TOKEN', csrf_token);
    request.onreadystatechange = function () {
    if (request.readyState === 4 && request.status === 200) {
        const mime = this.getResponseHeader('content-type');
        if (mime === 'text/csv' || mime === 'text/csv;charset=UTF-8') {
            // U pitanju je CSV fajl
            // Kreiramo blob od odgovora
            const blob = new Blob([this.response], { type: 'text/csv' });
            let filename = '';
            const disposition = this.getResponseHeader('Content-Disposition');
            if (disposition && disposition.indexOf('attachment') !== -1) {
                const filenameRegex = /filename[^;=\n]*=((['']).*?\2|[^;\n]*)/;
                const matches = filenameRegex.exec(disposition);
                if (matches != null && matches[1]) {
                    filename = matches[1].replace(/['']/g, '');
                }
            }
            // Kreiramo privremeni link, sakriven
            const a = document.createElement('a');
            a.style = 'display: none';
            document.body.appendChild(a);

            // Kreiramo privremeni URL za dohvacene podatke, kacimo to na link i klikcemo na njega
            const url = window.URL.createObjectURL(blob);
            a.href = url;
            a.download = filename;
            a.click();

            window.URL.revokeObjectURL(url);
        } else {
            const response = JSON.parse(request.responseText);
            const batch = response.batch;
            const response_data = JSON.parse(response.data);
            for (let i = 0, l = response_data.length; i < l; i++) {
                if (!response_data[i].ok) {
                    callCallbacksFailure(response_data[i], batch);
                }
            }
            const data_response = JSON.parse(request.responseText);
            const json_data = JSON.parse(data_response.data);
            callback(json_data[0].data, ctx);
        }
    } else if (request.status !== 200) {
        callCallbacksFailure(JSON.parse(request.responseText).data[0]);
    }
    };
    request.send(data);
};

let makeRequestBeacon = function (data) {
    const csrf_field = document.querySelector('#csrf-token');
    const csrf_token = csrf_field ? csrf_field.content : '';
    data.append('X-CSRF-TOKEN', csrf_token);
    navigator.sendBeacon('/laravel-tasks/public/ajax/data_raw', data);
};

let errorDispatch = function (data) {
    const detail = {
        error_code: data.code,
        error_message: data.message,
    }
    const event = new CustomEvent('Task.Error', {
        detail,
    });
    document.dispatchEvent(event);
};

if ('Task' in window === false) {
    window.Task = {};
}

if (window.Task.Main === undefined) {
    window.Task.Main = {};
}

/**
 * Dodaje u red za AJAX pozive
 * Na svakih n milisekundi se salju svi zahtevi zajedno
 * @param   {String}    controller_name      Ime komponente kojoj saljemo zahtev
 * @param   {String}    controller_method    Metoda komponente koju pozivamo
 * @param   {*}         params              Parametri koji ce biti poslati pri pozivu metode
 * @param   {*}         callbacks           callback funkcij(e)
 *                                          Kada je niz, prvi element ce biti success callback, a drugi failure callback
 *                                          Kada je objekat, moze da ima kljuceve success i failure
 *                                          Kada je funkcija, ona se koristi kao success callback
 * @param   {*}         ctx                 2. argument za callback funkciju
 * @param   {Boolean}   unprocessed         Da li saljemo sirov, neobradjeni zahtev (primer, za fajlove neophodno)
 * @param   {Boolean}   beacon              Da li slati kao XHR ili Beacon
 */
window.Task.Main.Ajax = function (
    controller_name,
    controller_method,
    params,
    callbacks,
    ctx,
    unprocessed,
    beacon
) {
    let callback_success = function () {};
    let callback_failure = function () {};

    if (typeof params === 'undefined') {
        params = {};
    }

    if (Array.isArray(callbacks)) {
        if (callbacks.length > 0 && typeof callbacks[0] === 'function') {
            callback_success = callbacks[0];
        }

        if (callbacks.length > 1 && typeof callbacks[1] === 'function') {
            callback_failure = callbacks[1];
        }
    } else if (typeof callbacks === 'function') {
        callback_success = callbacks;
        callback_failure = errorDispatch;
    } else if (typeof callbacks !== 'undefined') {
        if (typeof callbacks.success !== 'undefined') {
            callback_success = callbacks.success;
        }

        if (typeof callbacks.failure !== 'undefined') {
            callback_failure = callbacks.failure;
        } else {
            callback_failure = errorDispatch;
        }
    }

    if (typeof ctx === 'undefined') {
        ctx = {};
    }

    const key = controller_name + '--' + controller_method;
    const timestamp = new Date().getTime();
    config.timestamps[key] = timestamp;

    if (unprocessed || beacon) {
    const data = new FormData();
    buildFormData(data, params);
    data.append('controller_name', controller_name);
    data.append('controller_method', controller_method);
    data.append('timestamp', timestamp.toString());

    if (beacon && 'sendBeacon' in navigator) {
        makeRequestBeacon(data);
    } else {
        makeRequestRaw(data, callback_success, ctx);
    }
    } else {
    const batch = config.batch;
    config.callbacks.success[batch].push(callback_success);
    config.callbacks.failure[batch].push(callback_failure);
    config.ctx[batch].push(ctx);

    const queue = unprocessed ? config.queue_raw : config.queue[batch];
    queue.push({
        name: controller_name,
        controller_method: controller_method,
        timestamp: timestamp,
        params: params,
    });

    if (config.timeout_id) clearTimeout(config.timeout_id);

    config.timeout_id = setTimeout(makeRequestRegular, config.timeout_delay);
    }
};

window.Task.Main.Beacon = function (
    controller_name,
    controller_method,
    params
) {
    Task.Main.Ajax(
        controller_name,
        controller_method,
        params,
        undefined,
        undefined,
        undefined,
        true
    );
};
})();
