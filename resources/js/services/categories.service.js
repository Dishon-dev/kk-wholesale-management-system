import http from '@/bootstrap';

export async function tree() {
    const { data } = await http.get('/categories', { params: { tree: 1 } });
    return data;
}

export async function create(payload) {
    const { data } = await http.post('/categories', payload);
    return data;
}

export async function update(id, payload) {
    const { data } = await http.put(`/categories/${id}`, payload);
    return data;
}

export async function remove(id) {
    const { data } = await http.delete(`/categories/${id}`);
    return data;
}
