import http from '@/bootstrap';

export async function listForStore(storeId, params) {
    const { data } = await http.get(`/stores/${storeId}/stock`, { params });
    return data;
}

export async function movements(params) {
    const { data } = await http.get('/stock-movements', { params });
    return data;
}

export async function adjustments(params) {
    const { data } = await http.get('/stock-adjustments', { params });
    return data;
}

export async function createAdjustment(payload) {
    const { data } = await http.post('/stock-adjustments', payload);
    return data;
}

export async function approveAdjustment(id) {
    const { data } = await http.post(`/stock-adjustments/${id}/approve`);
    return data;
}

export async function rejectAdjustment(id, payload) {
    const { data } = await http.post(`/stock-adjustments/${id}/reject`, payload);
    return data;
}
