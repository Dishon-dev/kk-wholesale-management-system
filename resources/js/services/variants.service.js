import http from '@/bootstrap';

export async function create(productId, payload) {
    const { data } = await http.post(`/products/${productId}/variants`, payload);
    return data;
}

export async function update(id, payload) {
    const { data } = await http.put(`/variants/${id}`, payload);
    return data;
}

export async function remove(id) {
    const { data } = await http.delete(`/variants/${id}`);
    return data;
}

export async function search(params) {
    // look up a variant by SKU,barcode, or product name
    const { data } = await http.get('/variants/search', { params });
    return data;
}
