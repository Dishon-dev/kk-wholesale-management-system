import http from '@/bootstrap';

export async function list(params) {
    const { data } = await http.get('/products', { params });
    return data;
}

export async function get(id) {
    const { data } = await http.get(`/products/${id}`);
    return data;
}

export async function create(payload) {
    const { data } = await http.post('/products', payload);
    return data;
}

export async function update(id, payload) {
    const { data } = await http.put(`/products/${id}`, payload);
    return data;
}

export async function remove(id) {
    const { data } = await http.delete(`/products/${id}`);
    return data;
}
