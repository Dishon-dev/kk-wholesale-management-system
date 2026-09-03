import http from '@/bootstrap';

export async function list(params) {
    const { data } = await http.get('/stores', { params });
    return data;
}

export async function get(id) {
    const { data } = await http.get(`/stores/${id}`);
    return data;
}

export async function create(payload) {
    const { data } = await http.post('/stores', payload);
    return data;
}

export async function update(id, payload) {
    const { data } = await http.put(`/stores/${id}`, payload);
    return data;
}

export async function remove(id) {
    const { data } = await http.delete(`/stores/${id}`);
    return data;
}
