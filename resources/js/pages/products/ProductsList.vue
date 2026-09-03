<script setup>
import { onMounted } from 'vue';
import { usePagination } from '@/composables/usePagination';
import { usePermissions } from '@/composables/usePermissions';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import * as productsService from '@/services/products.service';
import { formatCurrency } from '@/utils/formatters';
import TableToolbar from '@/components/ui/TableToolbar.vue';
import BasePagination from '@/components/ui/BasePagination.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue';
import StatusTag from '@/components/ui/StatusTag.vue';

const { can } = usePermissions();
const toast = useToast();
const confirm = useConfirm();

const { items, loading, params, meta, load, reload, goToPage } = usePagination(productsService.list);

onMounted(load);

async function handleDelete(product) {
    const ok = await confirm({
        title: 'Delete this product?',
        message: `"${product.name}" and its variants will be archived. Existing sales history is kept.`,
        confirmLabel: 'Delete product',
    });
    if (!ok) return;

    try {
        await productsService.remove(product.id);
        toast.success('Product deleted.');
        load();
    } catch (error) {
        toast.error(error.message);
    }
}

function priceRange(product) {
    const prices = (product.variants ?? [])
        .map((variant) => Number(variant.price))
        .filter(Number.isFinite);

    if (!prices.length) {
        return '—';
    }

    const min = Math.min(...prices);
    const max = Math.max(...prices);

    return min === max
        ? formatCurrency(min)
        : `${formatCurrency(min)} – ${formatCurrency(max)}`;
}
</script>

<template>
    <div>
        <div class="mb-5 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold">Products</h1>
                <p class="text-sm text-ink-soft">Your catalog. Stock levels live on the Stock page, not here.</p>
            </div>
            <RouterLink v-if="can('products.create')" :to="{ name: 'products.create' }" class="btn-primary">
                Add product
            </RouterLink>
        </div>

        <div class="panel">
            <TableToolbar v-model="params.search" placeholder="Search by name, SKU or brand…" @update:modelValue="reload" />

            <LoadingSpinner v-if="loading" class="px-5 py-8" label="Loading products" />

            <EmptyState v-else-if="!items.length" title="No products yet" message="Add your first product to start recording stock and sales." />

            <table v-else class="data-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Brand</th>
                        <th>Variants</th>
                        <th>Selling price</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="product in items" :key="product.id">
                        <td>
                            <RouterLink :to="{ name: 'products.show', params: { id: product.id } }" class="font-medium text-ink hover:text-brand-500">
                                {{ product.name }}
                            </RouterLink>
                            <p class="text-xs text-ink-faint">{{ product.sku_prefix }}</p>
                        </td>
                        <td class="text-ink-soft">{{ product.brand?.name || '—' }}</td>
                        <td class="text-ink-soft">{{ product.variants?.length ?? 0 }}</td>
                        <td class="figures text-ink-soft">{{ priceRange(product) }}</td>
                        <td>
                            <StatusTag :label="product.is_active ? 'Active' : 'Inactive'" :tone="product.status ? 'positive' : 'neutral'" />
                        </td>
                        <td class="text-right">
                            <RouterLink v-if="can('products.update')" :to="{ name: 'products.edit', params: { id: product.id } }" class="btn-ghost inline-flex px-2 py-1 text-xs">
                                Edit
                            </RouterLink>
                            <button v-if="can('products.delete')" type="button" class="btn-ghost px-2 py-1 text-xs text-brick-500" @click="handleDelete(product)">
                                Delete
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <BasePagination v-if="items.length" :current-page="meta.currentPage" :last-page="meta.lastPage" :total="meta.total" @change="goToPage" />
        </div>
    </div>
</template>
