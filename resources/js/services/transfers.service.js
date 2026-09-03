import http from '@/bootstrap';

export async function list(params) {
    const { data } = await http.get('/stock-transfers', { params });
    return data;
}

export async function get(id) {
    const { data } = await http.get(`/stock-transfers/${id}`);
    return data;
}

export async function create(payload) {
    const { data } = await http.post('/stock-transfers', payload);
    return data;
}

export async function approve(id) {
    const { data } = await http.post(`/stock-transfers/${id}/approve`);
    return data;
}

export async function send(id, payload) {
    const { data } = await http.post(`/stock-transfers/${id}/send`, payload);
    return data;
}

export async function receive(id, payload) {
    const { data } = await http.post(`/stock-transfers/${id}/receive`, payload);
    return data;
}

export async function cancel(id, payload) {
    const { data } = await http.post(`/stock-transfers/${id}/cancel`, payload);
    return data;
}
