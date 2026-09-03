import http from '@/bootstrap';

export async function list(params) {
    const { data } = await http.get('/sales', { params });
    return data;
}

export async function get(id) {
    const { data } = await http.get(`/sales/${id}`);
    return data;
}

export async function create(payload) {
    const { data } = await http.post('/sales', payload);
    return data;
}

export async function voidSale(id, payload) {
    const { data } = await http.post(`/sales/${id}/void`, payload);
    return data;
}
