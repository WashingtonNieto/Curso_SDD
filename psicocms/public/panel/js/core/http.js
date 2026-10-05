export class HttpError extends Error {
    constructor(message, status, errors = {}) {
        super(message);
        this.status = status;
        this.errors = errors;
    }
}

const STATUS_MESSAGES = {
    401: 'Tu sesión ha caducado. Vuelve a iniciar sesión.',
    403: 'No tienes permiso para realizar esta acción.',
    404: 'No hemos encontrado lo que buscas.',
    419: 'La página ha caducado. Recárgala e inténtalo de nuevo.',
    429: 'Demasiadas peticiones seguidas. Espera un momento.',
    500: 'Ha ocurrido un error inesperado. Inténtalo de nuevo en unos minutos.',
};

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

export async function request(url, { method = 'GET', data = null, headers = {} } = {}) {
    const options = {
        method,
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken(),
            ...headers,
        },
        credentials: 'same-origin',
    };

    if (data instanceof FormData) {
        options.body = data;
    } else if (data !== null) {
        options.headers['Content-Type'] = 'application/json';
        options.body = JSON.stringify(data);
    }

    let response;

    try {
        response = await fetch(url, options);
    } catch {
        throw new HttpError('No hay conexión con el servidor. Comprueba tu conexión a internet.', 0);
    }

    const payload = await response.json().catch(() => ({}));

    if (!response.ok) {
        const message = payload.message && response.status !== 500
            ? payload.message
            : STATUS_MESSAGES[response.status] ?? STATUS_MESSAGES[500];

        throw new HttpError(message, response.status, payload.errors ?? {});
    }

    return payload;
}

export const http = {
    get: (url) => request(url),
    post: (url, data) => request(url, { method: 'POST', data }),
    put: (url, data) => request(url, { method: 'PUT', data }),
    patch: (url, data) => request(url, { method: 'PATCH', data }),
    delete: (url, data = null) => request(url, { method: 'DELETE', data }),
};
