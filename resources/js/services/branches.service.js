import http from '@/bootstrap';

export async function list(params) {
    const { data } = await http.get('/branches', { params });
    return data;
}

export async function get(id) {
    const { data } = await http.get(`/branches/${id}`);
    return data;
}

export async function create(payload) {
    const { data } = await http.post('/branches', payload);
    return data;
}

export async function update(id, payload) {
    const { data } = await http.put(`/branches/${id}`, payload);
    return data;
}

export async function remove(id) {
    const { data } = await http.delete(`/branches/${id}`);
    return data;
}
