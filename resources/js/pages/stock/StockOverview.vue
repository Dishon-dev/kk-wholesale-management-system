<script setup>
import { onMounted, ref, watch } from 'vue';
import { usePagination } from '@/composables/usePagination';
import { usePermissions } from '@/composables/usePermissions';
import { useAuthStore } from '@/stores/auth';
import * as storesService from '@/services/stores.service';
import * as stockService from '@/services/stock.service';
import { formatNumber } from '@/utils/formatters';
import TableToolbar from '@/components/ui/TableToolbar.vue';
import BasePagination from '@/components/ui/BasePagination.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue';
import StatusTag from '@/components/ui/StatusTag.vue';
import StockAdjustmentForm from '@/components/domain/StockAdjustmentForm.vue';

const { can } = usePermissions();
const auth = useAuthStore();

const stores = ref([]);
const activeStoreId = ref('');

const { items, loading, params, meta, load, reload, goToPage, setFilter } = usePagination(
    (queryParams) => stockService.listForStore(activeStoreId.value, queryParams),
    { extraParams: { low_stock_only: false } }
);

onMounted(async () => {
    const { data } = await storesService.list({ per_page: 100, status: 1 });
    stores.value = data;

    // Store Managers only ever see their own store; default everyone else
    // to the first store in whatever scoped list the API returned.
    activeStoreId.value = auth.user?.store_id ?? data[0]?.id ?? '';
    if (activeStoreId.value) load();
});

watch(activeStoreId, () => {
    if (activeStoreId.value) load();
});

const adjustingStock = ref(null);

function handleSaved() {
    adjustingStock.value = null;
    load();
}

function availableQuantity(stock) {
    return stock.quantity - stock.reserved_quantity;
}

function stockTone(stock) {
    const available = availableQuantity(stock);
    if (available <= 0) return 'negative';
    if (available <= stock.low_stock_threshold) return 'warning';
    return 'positive';
}
</script>

<template>
    <div>
        <div class="mb-5 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold">Stock by store</h1>
                <p class="text-sm text-ink-soft">Current quantity on hand for every product variant.</p>
            </div>
        </div>

        <div class="panel">
            <TableToolbar v-model="params.search" placeholder="Search by product or SKU…" @update:modelValue="reload">
                <template #filters>
                    <select
                        v-if="stores.length > 1"
                        v-model="activeStoreId"
                        class="field-input w-auto"
                    >
                        <option v-for="store in stores" :key="store.id" :value="store.id">{{ store.name }}</option>
                    </select>

                    <label class="flex items-center gap-2 text-sm text-ink-soft">
                        <input
                            type="checkbox"
                            class="rounded border-border"
                            @change="setFilter('low_stock_only', $event.target.checked)"
                        />
                        Low stock only
                    </label>
                </template>
            </TableToolbar>

            <LoadingSpinner v-if="loading" class="px-5 py-8" label="Loading stock" />

            <EmptyState v-else-if="!items.length" title="No stock recorded" message="Stock will appear here once items are received at this store." />

            <table v-else class="data-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Variant</th>
                        <th>SKU</th>
                        <th>On hand</th>
                        <th>Reserved</th>
                        <th>Available</th>
                        <th>Threshold</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="stock in items" :key="stock.id">
                        <td class="font-medium">{{ stock.product_variant?.product?.name }}</td>
                        <td class="text-ink-soft">{{ stock.product_variant?.name }}</td>
                        <td class="figures text-ink-faint">{{ stock.product_variant?.sku }}</td>
                        <td class="figures">{{ formatNumber(stock.quantity) }}</td>
                        <td class="figures text-ink-soft">{{ formatNumber(stock.reserved_quantity) }}</td>
                        <td class="figures font-medium">{{ formatNumber(availableQuantity(stock)) }}</td>
                        <td class="figures text-ink-soft">{{ formatNumber(stock.low_stock_threshold) }}</td>
                        <td>
                            <StatusTag
                                :label="availableQuantity(stock) <= 0 ? 'Out of stock' : (availableQuantity(stock) <= stock.low_stock_threshold ? 'Low stock' : 'In stock')"
                                :tone="stockTone(stock)"
                            />
                        </td>
                        <td class="text-right">
                            <button v-if="can('stock.adjust')" type="button" class="btn-ghost px-2 py-1 text-xs" @click="adjustingStock = stock">
                                Adjust
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <BasePagination v-if="items.length" :current-page="meta.currentPage" :last-page="meta.lastPage" :total="meta.total" @change="goToPage" />
        </div>

        <StockAdjustmentForm v-if="adjustingStock" :stock="adjustingStock" @close="adjustingStock = null" @saved="handleSaved" />
    </div>
</template>
