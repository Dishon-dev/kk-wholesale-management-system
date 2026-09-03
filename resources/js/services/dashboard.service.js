import http from '@/bootstrap';

export async function summary(params) {
    const { data } = await http.get('/dashboard/summary', { params });
    return data;
}

export async function salesTrend(params) {
    const { data } = await http.get('/dashboard/sales-trend', { params });
    return data;
}

export async function topProducts(params) {
    const { data } = await http.get('/dashboard/top-products', { params });
    return data;
}
