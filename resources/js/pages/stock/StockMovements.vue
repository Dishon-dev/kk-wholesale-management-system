<script setup>
import { onMounted, ref } from 'vue';
import { usePagination } from '@/composables/usePagination';
import * as stockService from '@/services/stock.service';
import * as storesService from '@/services/stores.service';
import { formatDateTime, formatNumber } from '@/utils/formatters';
import { MOVEMENT_TYPES } from '@/utils/constants';
import TableToolbar from '@/components/ui/TableToolbar.vue';
import BasePagination from '@/components/ui/BasePagination.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue';
import StatusTag from '@/components/ui/StatusTag.vue';

const stores = ref([]);

const { items, loading, params, meta, load, reload, goToPage, setFilter } = usePagination(stockService.movements, {
    perPage: 25,
    extraParams: { store_id: '', movement_type: '', date_from: '', date_to: '' },
});

onMounted(async () => {
    const { data } = await storesService.list({ per_page: 100, status: 1 });
    stores.value = data;
    load();
});

function movementMeta(type) {
    return MOVEMENT_TYPES[type] ?? { label: type, tone: 'neutral', sign: '' };
}

function referenceLabel(entry) {
    if (!entry.reference_type) return '';
    const type = entry.reference_type.split('\\').pop();
    return `${type} #${entry.reference_id}`;
}
</script>

<template>
    <div>
        <div class="mb-5">
            <h1 class="text-xl font-semibold">Stock movements</h1>
            <p class="text-sm text-ink-soft">A complete, immutable history of every change to stock — the source of
                truth for "what happened".</p>
        </div>

        <div class="panel">
            <TableToolbar v-model="params.search" placeholder="Search by SKU or product…" @update:modelValue="reload">
                <template #filters>
                    <select class="field-input w-auto" @change="setFilter('store_id', $event.target.value)">
                        <option value="">All stores</option>
                        <option v-for="store in stores" :key="store.id" :value="store.id">{{ store.name }}</option>
                    </select>

                    <select class="field-input w-auto" @change="setFilter('movement_type', $event.target.value)">
                        <option value="">All movement types</option>
                        <option v-for="(meta, key) in MOVEMENT_TYPES" :key="key" :value="key">{{ meta.label }}</option>
                    </select>

                    <input type="date" class="field-input w-auto"
                        @change="setFilter('date_from', $event.target.value)" />
                    <input type="date" class="field-input w-auto" @change="setFilter('date_to', $event.target.value)" />
                </template>
            </TableToolbar>

            <LoadingSpinner v-if="loading" class="px-5 py-8" label="Loading movements" />

            <EmptyState v-else-if="!items.length" title="No movements match those filters" />

            <table v-else class="data-table">
                <thead>
                    <tr>
                        <th>When</th>
                        <th>Store</th>
                        <th>Product</th>
                        <th>Type</th>
                        <th>Change</th>
                        <th>Balance after</th>
                        <th>Reference</th>
                        <th>By</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="entry in items" :key="entry.id">
                        <td class="text-ink-soft">{{ formatDateTime(entry.created_at) }}</td>
                        <td>{{ entry.stock?.store?.name }}</td>
                        <td>
                            <p class="font-medium text-ink">{{ entry.stock?.product_variant?.product?.name }}</p>
                            <p class="text-xs text-ink-faint">{{ entry.stock?.product_variant?.name }}</p>
                        </td>
                        <td>
                            <StatusTag :label="movementMeta(entry.movement_type).label"
                                :tone="movementMeta(entry.movement_type).tone" />
                        </td>
                        <td class="figures" :class="entry.change < 0 ? 'text-brick-500' : 'text-moss-700'">
                            {{ entry.change > 0 ? '+' : '' }}{{ formatNumber(entry.change) }}
                        </td>
                        <td class="figures font-medium">{{ formatNumber(entry.balance_after) }}</td>
                        <td class="text-ink-faint">{{ referenceLabel(entry) }}</td>
                        <td class="text-ink-soft">{{ entry.performed_by_user?.name ?? '' }}</td>
                    </tr>
                </tbody>
            </table>

            <BasePagination v-if="items.length" :current-page="meta.currentPage" :last-page="meta.lastPage"
                :total="meta.total" @change="goToPage" />
        </div>
    </div>
</template>
