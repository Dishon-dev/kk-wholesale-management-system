import axios from 'axios';

const http = axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL ?? '/api/v1',
    withCredentials: true,
    withXSRFToken: true,
    headers: {
        Accept: 'application/json',
    },
});

export async function primeCsrfCookie() {
    await axios.get('/sanctum/csrf-cookie', { baseURL: '/', withCredentials: true });
}

let csrfRetried = false;

http.interceptors.response.use(
    (response) => response,
    async (error) => {
        const status = error.response?.status;
        if (status === 419 && !csrfRetried) {
            csrfRetried = true;
            await primeCsrfCookie();
            csrfRetried = false;
            return http.request(error.config);
        }

        return Promise.reject(normalizeError(error));
    }
);

function normalizeError(error) {
    if (!error.response) {
        return {
            status: 0,
            message: 'Could not reach the server. Check your connection and try again.',
            fieldErrors: {},
        };
    }

    const { status, data } = error.response;

    return {
        status,
        message: data?.message ?? defaultMessageFor(status),
        fieldErrors: data?.errors ?? {},
    };
}

function defaultMessageFor(status) {
    switch (status) {
        case 401:
            return 'Your session has expired. Please sign in again.';
        case 403:
            return "You don't have permission to do that.";
        case 404:
            return 'That record could not be found.';
        case 409:
            return 'This action conflicts with the current state of the record.';
        case 422:
            return 'Some fields need attention.';
        case 429:
            return 'Too many requests — please slow down and try again shortly.';
        default:
            return 'Something went wrong. Please try again.';
    }
}

export default http;
