<script setup>
import { onMounted, ref } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { usePermissions } from '@/composables/usePermissions';
import * as dashboardService from '@/services/dashboard.service';
import * as activityService from '@/services/activityLogs.service';
import { formatCurrency, formatNumber, formatRelativeTime } from '@/utils/formatters';
import StatCard from '@/components/ui/StatCard.vue';
import TrendChart from '@/components/charts/TrendChart.vue';
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue';
import EmptyState from '@/components/ui/EmptyState.vue';

const auth = useAuthStore();
const { can } = usePermissions();

const loading = ref(true);
const summary = ref(null);
const trend = ref({ labels: [], values: [] });
const topProducts = ref([]);
const recentActivity = ref([]);

onMounted(async () => {
    try {
        const requests = [dashboardService.summary(), dashboardService.salesTrend({ days: 14 })];
        if (can('reports.view')) requests.push(dashboardService.topProducts({ limit: 5 }));
        if (can('activity.view')) requests.push(activityService.list({ per_page: 6 }));

        const [summaryRes, trendRes, topRes, activityRes] = await Promise.all(requests);

        summary.value = summaryRes.data;
        trend.value = trendRes.data;
        if (topRes) topProducts.value = topRes.data;
        if (activityRes) recentActivity.value = activityRes.data;
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <div>
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold">Good to see you, {{ auth.user?.name?.split(' ')[0] }}</h1>
                <p class="text-sm text-ink-soft">Here's how things stand right now.</p>
            </div>
        </div>

        <LoadingSpinner v-if="loading" label="Loading dashboard" />

        <template v-else-if="summary">
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <StatCard label="Today's sales" :value="formatCurrency(summary.todays_sales_total)" :hint="`${summary.todays_sales_count} transactions`" />
                <StatCard label="Stock on hand" :value="formatCurrency(summary.total_stock_value)" hint="Valued at cost price" />
                <StatCard
                    label="Low stock items"
                    :value="formatNumber(summary.low_stock_count)"
                    hint="Below their threshold"
                    :tone="summary.low_stock_count > 0 ? 'warning' : 'neutral'"
                />
                <StatCard
                    label="Out of stock"
                    :value="formatNumber(summary.out_of_stock_count)"
                    :hint="`${summary.pending_transfers_count} transfers pending`"
                    :tone="summary.out_of_stock_count > 0 ? 'negative' : 'neutral'"
                />
            </div>

            <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-3">
                <div class="panel p-5 lg:col-span-2">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-sm font-semibold text-ink">Sales, last 14 days</h2>
                    </div>
                    <TrendChart v-if="trend.values.length" :labels="trend.labels" :values="trend.values" />
                    <EmptyState v-else title="No sales recorded yet" message="Sales will appear here once transactions start coming in." />
                </div>

                <div class="panel p-5">
                    <h2 class="mb-4 text-sm font-semibold text-ink">Best sellers this week</h2>
                    <ul v-if="topProducts.length" class="space-y-3">
                        <li v-for="product in topProducts" :key="product.name" class="flex items-center justify-between text-sm">
                            <span class="text-ink">{{ product.name }}</span>
                            <span class="figures text-ink-soft">{{ formatNumber(product.quantity_sold) }} units</span>
                        </li>
                    </ul>
                    <p v-else class="text-sm text-ink-faint">Nothing sold yet this week.</p>
                </div>
            </div>

            <div v-if="recentActivity.length" class="panel mt-6 p-5">
                <h2 class="mb-4 text-sm font-semibold text-ink">Recent activity</h2>
                <ul class="space-y-3">
                    <li v-for="entry in recentActivity" :key="entry.id" class="flex items-start justify-between text-sm">
                        <div>
                            <span class="font-medium text-ink">{{ entry.user?.name ?? 'System' }}</span>
                            <span class="text-ink-soft"> {{ entry.description }}</span>
                        </div>
                        <span class="shrink-0 text-xs text-ink-faint">{{ formatRelativeTime(entry.created_at) }}</span>
                    </li>
                </ul>
            </div>
        </template>
    </div>
</template>
