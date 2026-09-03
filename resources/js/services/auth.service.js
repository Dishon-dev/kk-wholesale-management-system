import http, { primeCsrfCookie } from '@/bootstrap';

export async function login({ email, password, remember = false }) {
    await primeCsrfCookie();
    const response = await http.post('/auth/login', { email, password, remember });
    return response.data;
}

export async function logout() {
    const response = await http.post('/auth/logout');
    return response.data;
}

export async function me() {
    const response = await http.get('/auth/me');
    return response.data;
}

export async function changePassword(payload) {
    const response = await http.put('/auth/password', payload);
    return response.data;
}
