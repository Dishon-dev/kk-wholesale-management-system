import http from '@/bootstrap';

export async function list(params) {
    const { data } = await http.get('/activity-logs', { params });
    return data;
}
