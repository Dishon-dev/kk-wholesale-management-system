<script setup>
import { computed, onMounted, ref } from 'vue';
import { usePermissions } from '@/composables/usePermissions';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import * as salesService from '@/services/sales.service';
import { formatCurrency, formatDateTime } from '@/utils/formatters';
import { SALE_STATUSES } from '@/utils/constants';
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue';
import StatusTag from '@/components/ui/StatusTag.vue';

const props = defineProps({
    id: { type: [String, Number], required: true },
});

const { can } = usePermissions();
const toast = useToast();
const confirm = useConfirm();

const loading = ref(true);
const sale = ref(null);

async function load() {
    loading.value = true;
    try {
        const { data } = await salesService.get(props.id);
        sale.value = data;
    } finally {
        loading.value = false;
    }
}

onMounted(load);

const canVoid = computed(() => sale.value?.status === 'COMPLETED' && can('sales.void'));

async function handleVoid() {
    const ok = await confirm({
        title: 'Void this sale?',
        message: 'The items will be returned to stock and this cannot be undone.',
        confirmLabel: 'Void sale',
    });
    if (!ok) return;

    try {
        await salesService.voidSale(sale.value.id, {});
        toast.success('Sale voided and stock returned.');
        load();
    } catch (error) {
        toast.error(error.message);
    }
}
</script>

<template>
    <LoadingSpinner v-if="loading" label="Loading sale" />

    <div v-else-if="sale" class="mx-auto max-w-2xl">
        <div class="mb-5 flex items-start justify-between">
            <div>
                <h1 class="font-mono text-xl font-semibold">{{ sale.invoice_number }}</h1>
                <p class="text-sm text-ink-soft">{{ sale.store?.name }} · {{ formatDateTime(sale.created_at) }}</p>
            </div>
            <StatusTag :label="SALE_STATUSES[sale.status]?.label ?? sale.status" :tone="SALE_STATUSES[sale.status]?.tone" />
        </div>

        <div class="panel">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Unit price</th>
                        <th>Discount</th>
                        <th>Line total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in sale.items" :key="item.id">
                        <td>
                            <p class="font-medium text-ink">{{ item.product_variant?.product?.name }}</p>
                            <p class="text-xs text-ink-faint">{{ item.product_variant?.name }} · {{ item.product_variant?.sku }}</p>
                        </td>
                        <td class="figures">{{ item.quantity }}</td>
                        <td class="figures text-ink-soft">{{ formatCurrency(item.unit_price) }}</td>
                        <td class="figures text-ink-soft">{{ formatCurrency(item.discount) }}</td>
                        <td class="figures font-medium">{{ formatCurrency(item.line_total) }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="space-y-1.5 border-t border-border px-5 py-4 text-sm">
                <div class="flex justify-between"><span class="text-ink-soft">Subtotal</span><span class="figures">{{ formatCurrency(sale.subtotal) }}</span></div>
                <div class="flex justify-between"><span class="text-ink-soft">Discount</span><span class="figures">-{{ formatCurrency(sale.discount) }}</span></div>
                <div class="flex justify-between"><span class="text-ink-soft">Tax</span><span class="figures">{{ formatCurrency(sale.tax) }}</span></div>
                <div class="flex justify-between border-t border-border pt-1.5 text-base font-semibold text-ink">
                    <span>Grand total</span><span class="figures">{{ formatCurrency(sale.grand_total) }}</span>
                </div>
            </div>
        </div>

        <p class="mt-4 text-xs text-ink-faint">Sold by {{ sale.cashier?.name }}</p>

        <div v-if="canVoid" class="mt-5 flex justify-end">
            <button type="button" class="btn-danger" @click="handleVoid">Void sale</button>
        </div>
    </div>
</template>
