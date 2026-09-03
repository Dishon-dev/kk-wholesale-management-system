<script setup>
import { onMounted, ref } from 'vue';
import { usePagination } from '@/composables/usePagination';
import * as storesService from '@/services/stores.service';
import * as salesService from '@/services/sales.service';
import { formatCurrency, formatDateTime } from '@/utils/formatters';
import { SALE_STATUSES } from '@/utils/constants';
import TableToolbar from '@/components/ui/TableToolbar.vue';
import BasePagination from '@/components/ui/BasePagination.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue';
import StatusTag from '@/components/ui/StatusTag.vue';

const stores = ref([]);

const { items, loading, params, meta, load, reload, goToPage, setFilter } = usePagination(salesService.list, {
    extraParams: { store_id: '', status: '', date_from: '', date_to: '' },
});

onMounted(async () => {
    const { data } = await storesService.list({ per_page: 100, status: 1 });
    stores.value = data;
    load();
});
</script>

<template>
    <div>
        <div class="mb-5">
            <h1 class="text-xl font-semibold">Sales history</h1>
            <p class="text-sm text-ink-soft">Every transaction recorded across your stores.</p>
        </div>

        <div class="panel">
            <TableToolbar v-model="params.search" placeholder="Search by invoice number…" @update:modelValue="reload">
                <template #filters>
                    <select v-if="stores.length > 1" class="field-input w-auto" @change="setFilter('store_id', $event.target.value)">
                        <option value="">All stores</option>
                        <option v-for="store in stores" :key="store.id" :value="store.id">{{ store.name }}</option>
                    </select>
                    <select class="field-input w-auto" @change="setFilter('status', $event.target.value)">
                        <option value="">All statuses</option>
                        <option v-for="(meta, key) in SALE_STATUSES" :key="key" :value="key">{{ meta.label }}</option>
                    </select>
                    <input type="date" class="field-input w-auto" @change="setFilter('date_from', $event.target.value)" />
                    <input type="date" class="field-input w-auto" @change="setFilter('date_to', $event.target.value)" />
                </template>
            </TableToolbar>

            <LoadingSpinner v-if="loading" class="px-5 py-8" label="Loading sales" />

            <EmptyState v-else-if="!items.length" title="No sales recorded" />

            <table v-else class="data-table">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Store</th>
                        <th>Cashier</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>When</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="sale in items" :key="sale.id" class="cursor-pointer" @click="$router.push({ name: 'sales.show', params: { id: sale.id } })">
                        <td class="figures font-medium">{{ sale.invoice_number }}</td>
                        <td>{{ sale.store?.name }}</td>
                        <td class="text-ink-soft">{{ sale.cashier?.name }}</td>
                        <td class="text-ink-soft">{{ sale.items_count ?? sale.items?.length ?? 0 }}</td>
                        <td class="figures font-medium">{{ formatCurrency(sale.grand_total) }}</td>
                        <td>
                            <StatusTag :label="SALE_STATUSES[sale.status]?.label ?? sale.status" :tone="SALE_STATUSES[sale.status]?.tone" />
                        </td>
                        <td class="text-ink-soft">{{ formatDateTime(sale.created_at) }}</td>
                    </tr>
                </tbody>
            </table>

            <BasePagination v-if="items.length" :current-page="meta.currentPage" :last-page="meta.lastPage" :total="meta.total" @change="goToPage" />
        </div>
    </div>
</template>
