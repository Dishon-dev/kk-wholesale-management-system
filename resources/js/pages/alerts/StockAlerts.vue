<script setup>
import { onMounted } from 'vue';
import { usePagination } from '@/composables/usePagination';
import { usePermissions } from '@/composables/usePermissions';
import { useToast } from '@/composables/useToast';
import * as alertsService from '@/services/alerts.service';
import { formatDateTime, formatNumber } from '@/utils/formatters';
import { ALERT_TYPES } from '@/utils/constants';
import TableToolbar from '@/components/ui/TableToolbar.vue';
import BasePagination from '@/components/ui/BasePagination.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue';
import StatusTag from '@/components/ui/StatusTag.vue';

const { can } = usePermissions();
const toast = useToast();

const { items, loading, params, meta, load, reload, goToPage, setFilter } = usePagination(alertsService.list, {
    extraParams: { resolved: '0' },
});

onMounted(load);

async function handleResolve(alert) {
    try {
        await alertsService.resolve(alert.id);
        toast.success('Alert marked as resolved.');
        load();
    } catch (error) {
        toast.error(error.message);
    }
}
</script>

<template>
    <div>
        <div class="mb-5">
            <h1 class="text-xl font-semibold">Stock alerts</h1>
            <p class="text-sm text-ink-soft">Items that have hit their low-stock threshold or run out entirely.</p>
        </div>

        <div class="panel">
            <TableToolbar v-model="params.search" placeholder="Search by product…" @update:modelValue="reload">
                <template #filters>
                    <select class="field-input w-auto" @change="setFilter('resolved', $event.target.value)">
                        <option value="0">Unresolved</option>
                        <option value="1">Resolved</option>
                        <option value="">All</option>
                    </select>
                    <select class="field-input w-auto" @change="setFilter('alert_type', $event.target.value)">
                        <option value="">All types</option>
                        <option v-for="(meta, key) in ALERT_TYPES" :key="key" :value="key">{{ meta.label }}</option>
                    </select>
                </template>
            </TableToolbar>

            <LoadingSpinner v-if="loading" class="px-5 py-8" label="Loading alerts" />

            <EmptyState v-else-if="!items.length" title="No alerts" message="Nothing needs attention right now." />

            <table v-else class="data-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Store</th>
                        <th>Type</th>
                        <th>Raised</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="alert in items" :key="alert.id">
                        <td>
                            <p class="font-medium text-ink">{{ alert.stock?.product_variant?.product?.name }}</p>
                            <p class="text-xs text-ink-faint">{{ alert.stock?.product_variant?.name }} · {{ formatNumber(alert.stock?.quantity) }} on hand</p>
                        </td>
                        <td>{{ alert.stock?.store?.name }}</td>
                        <td>
                            <StatusTag :label="ALERT_TYPES[alert.alert_type]?.label" :tone="ALERT_TYPES[alert.alert_type]?.tone" />
                        </td>
                        <td class="text-ink-soft">{{ formatDateTime(alert.created_at) }}</td>
                        <td>
                            <StatusTag :label="alert.resolved_at ? 'Resolved' : 'Open'" :tone="alert.resolved_at ? 'positive' : 'neutral'" />
                        </td>
                        <td class="text-right">
                            <button
                                v-if="!alert.resolved_at && can('alerts.view')"
                                type="button"
                                class="btn-ghost px-2 py-1 text-xs"
                                @click="handleResolve(alert)"
                            >
                                Mark resolved
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <BasePagination v-if="items.length" :current-page="meta.currentPage" :last-page="meta.lastPage" :total="meta.total" @change="goToPage" />
        </div>
    </div>
</template>
