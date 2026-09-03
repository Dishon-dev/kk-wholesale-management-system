<script setup>
import { onMounted, ref } from 'vue';
import { usePermissions } from '@/composables/usePermissions';
import * as productsService from '@/services/products.service';
import { formatCurrency, formatNumber } from '@/utils/formatters';
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue';
import StatusTag from '@/components/ui/StatusTag.vue';

const props = defineProps({
    id: { type: [String, Number], required: true },
});

const { can } = usePermissions();
const loading = ref(true);
const product = ref(null);

onMounted(async () => {
    try {
        const { data } = await productsService.get(props.id);
        product.value = data;
    } finally {
        loading.value = false;
    }
});

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
    <LoadingSpinner v-if="loading" label="Loading product" />

    <div v-else-if="product">
        <div class="mb-5 flex items-start justify-between">
            <div>
                <h1 class="text-xl font-semibold">{{ product.name }}</h1>
                <p class="text-sm text-ink-soft">{{ product.sku_prefix }} · {{ product.brand || 'No brand' }}</p>
            </div>
            <div class="flex items-center gap-2">
                <StatusTag :label="product.status ? 'Active' : 'Inactive'" :tone="product.status ? 'positive' : 'neutral'" />
                <RouterLink v-if="can('products.update')" :to="{ name: 'products.edit', params: { id: product.id } }" class="btn-secondary">
                    Edit product
                </RouterLink>
            </div>
        </div>

        <p v-if="product.description" class="mb-6 max-w-2xl text-sm text-ink-soft">{{ product.description }}</p>

        <div v-for="variant in product.variants" :key="variant.id" class="panel mb-4">
            <div class="panel-header">
                <div>
                    <p class="text-sm font-semibold text-ink">{{ variant.name }}</p>
                    <p class="text-xs text-ink-faint">SKU {{ variant.sku }} <span v-if="variant.barcode">· Barcode {{ variant.barcode }}</span></p>
                </div>
                <div class="flex gap-6 text-right text-sm">
                    <div>
                        <p class="text-xs text-ink-faint">Cost</p>
                        <p class="figures text-ink">{{ formatCurrency(variant.cost_price) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-ink-faint">Selling price</p>
                        <p class="figures text-ink">{{ formatCurrency(variant.selling_price) }}</p>
                    </div>
                </div>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Store</th>
                        <th>On hand</th>
                        <th>Reserved</th>
                        <th>Available</th>
                        <th>Low stock at</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody v-if="variant.stocks?.length">
                    <tr v-for="stock in variant.stocks" :key="stock.id">
                        <td class="font-medium">{{ stock.store?.name }}</td>
                        <td class="figures text-ink-soft">{{ formatNumber(stock.quantity) }}</td>
                        <td class="figures text-ink-soft">{{ formatNumber(stock.reserved_quantity) }}</td>
                        <td class="figures font-medium">{{ formatNumber(availableQuantity(stock)) }}</td>
                        <td class="figures text-ink-soft">{{ formatNumber(stock.low_stock_threshold) }}</td>
                        <td>
                            <StatusTag
                                :label="availableQuantity(stock) <= 0 ? 'Out of stock' : (availableQuantity(stock) <= stock.low_stock_threshold ? 'Low stock' : 'In stock')"
                                :tone="stockTone(stock)"
                            />
                        </td>
                    </tr>
                </tbody>
                <tbody v-else>
                    <tr>
                        <td colspan="6" class="py-6 text-center text-sm text-ink-faint">
                            Not stocked at any store yet.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
