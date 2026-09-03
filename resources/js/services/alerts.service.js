import http from '@/bootstrap';

export async function list(params) {
    const { data } = await http.get('/stock-alerts', { params });
    return data;
}

export async function resolve(id) {
    const { data } = await http.post(`/stock-alerts/${id}/resolve`);
    return data;
}

export async function unreadCount() {
    const { data } = await http.get('/stock-alerts/unread-count');
    return data;
}
