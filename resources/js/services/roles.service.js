import http from '@/bootstrap';

export async function list() {
    const response = await http.get('/roles');
    return response.data;
}

export async function get(id) {
    const { data } = await http.get(`/roles/${id}`);
    return data;
}

export async function create(payload) {
    const { data } = await http.post('/roles', payload);
    return data;
}

export async function update(id, payload) {
    const { data } = await http.put(`/roles/${id}`, payload);
    return data;
}

export async function remove(id) {
    const { data } = await http.delete(`/roles/${id}`);
    return data;
}

export async function permissions() {
    const { data } = await http.get('/permissions');
    return data;
}

export async function createPermission(payload) {
    const { data } = await http.post('/permissions', payload);
    return data;
}
