import http from '@/bootstrap';

export async function list(params) {
    const { data } = await http.get('/users', { params });
    return data;
}

export async function get(id) {
    const { data } = await http.get(`/users/${id}`);
    return data;
}

export async function create(payload) {
    const { data } = await http.post('/users', payload);
    return data;
}

export async function update(id, payload) {
    const { data } = await http.put(`/users/${id}`, payload);
    return data;
}

export async function remove(id) {
    const { data } = await http.delete(`/users/${id}`);
    return data;
}

export async function setActive(id, isActive) {
    const { data } = await http.put(`/users/${id}/status`, { is_active: isActive });
    return data;
}

export async function resetPassword(id) {
    const { data } = await http.post(`/users/${id}/reset-password`);
    return data;
}
