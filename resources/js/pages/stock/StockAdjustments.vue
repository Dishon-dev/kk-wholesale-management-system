<script setup>
import { onMounted, ref } from 'vue';
import { usePagination } from '@/composables/usePagination';
import { usePermissions } from '@/composables/usePermissions';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import * as stockService from '@/services/stock.service';
import { formatDateTime, formatNumber } from '@/utils/formatters';
import { ADJUSTMENT_TYPES, ADJUSTMENT_STATUSES } from '@/utils/constants';
import TableToolbar from '@/components/ui/TableToolbar.vue';
import BasePagination from '@/components/ui/BasePagination.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue';
import StatusTag from '@/components/ui/StatusTag.vue';
import StockAdjustmentForm from '@/components/domain/StockAdjustmentForm.vue';

const { can } = usePermissions();
const toast = useToast();
const confirm = useConfirm();

const { items, loading, params, meta, load, reload, goToPage, setFilter } = usePagination(stockService.adjustments, {
    extraParams: { status: '' },
});

onMounted(load);

const formOpen = ref(false);

function handleSaved() {
    formOpen.value = false;
    toast.success('Adjustment submitted for approval.');
    load();
}

async function handleApprove(adjustment) {
    const ok = await confirm({
        title: 'Approve this adjustment?',
        message: `Stock for this item will be ${adjustment.adjustment_type === 'INCREASE' ? 'increased' : 'decreased'} by ${adjustment.quantity} units immediately.`,
        confirmLabel: 'Approve',
        tone: 'primary',
    });
    if (!ok) return;

    try {
        await stockService.approveAdjustment(adjustment.id);
        toast.success('Adjustment approved and applied.');
        load();
    } catch (error) {
        toast.error(error.message);
    }
}

async function handleReject(adjustment) {
    const ok = await confirm({
        title: 'Reject this adjustment?',
        message: 'The stock level will remain unchanged.',
        confirmLabel: 'Reject',
    });
    if (!ok) return;

    try {
        await stockService.rejectAdjustment(adjustment.id, {});
        toast.success('Adjustment rejected.');
        load();
    } catch (error) {
        toast.error(error.message);
    }
}
</script>

<template>
    <div>
        <div class="mb-5 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold">Stock adjustments</h1>
                <p class="text-sm text-ink-soft">Manual corrections for damage, loss, found stock, or count discrepancies — every one needs a reason and, for increases beyond a small amount, sign-off.</p>
            </div>
            <button v-if="can('stock.adjust')" type="button" class="btn-primary" @click="formOpen = true">
                New adjustment
            </button>
        </div>

        <div class="panel">
            <TableToolbar v-model="params.search" placeholder="Search by product or SKU…" @update:modelValue="reload">
                <template #filters>
                    <select class="field-input w-auto" @change="setFilter('status', $event.target.value)">
                        <option value="">All statuses</option>
                        <option v-for="(meta, key) in ADJUSTMENT_STATUSES" :key="key" :value="key">{{ meta.label }}</option>
                    </select>
                </template>
            </TableToolbar>

            <LoadingSpinner v-if="loading" class="px-5 py-8" label="Loading adjustments" />

            <EmptyState v-else-if="!items.length" title="No adjustment requests" />

            <table v-else class="data-table">
                <thead>
                    <tr>
                        <th>Requested</th>
                        <th>Store / product</th>
                        <th>Type</th>
                        <th>Qty</th>
                        <th>Reason</th>
                        <th>Requested by</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="adjustment in items" :key="adjustment.id">
                        <td class="text-ink-soft">{{ formatDateTime(adjustment.created_at) }}</td>
                        <td>
                            <p class="font-medium text-ink">{{ adjustment.stock?.product_variant?.product?.name }}</p>
                            <p class="text-xs text-ink-faint">{{ adjustment.stock?.store?.name }}</p>
                        </td>
                        <td>
                            <StatusTag :label="ADJUSTMENT_TYPES[adjustment.adjustment_type]?.label" :tone="ADJUSTMENT_TYPES[adjustment.adjustment_type]?.tone" />
                        </td>
                        <td class="figures">{{ formatNumber(adjustment.quantity) }}</td>
                        <td class="max-w-xs truncate text-ink-soft" :title="adjustment.reason">{{ adjustment.reason }}</td>
                        <td class="text-ink-soft">{{ adjustment.performed_by_user?.name }}</td>
                        <td>
                            <StatusTag :label="ADJUSTMENT_STATUSES[adjustment.status]?.label" :tone="ADJUSTMENT_STATUSES[adjustment.status]?.tone" />
                        </td>
                        <td class="text-right">
                            <template v-if="adjustment.status === 'PENDING' && can('stock.adjust.approve')">
                                <button type="button" class="btn-ghost px-2 py-1 text-xs text-moss-700" @click="handleApprove(adjustment)">
                                    Approve
                                </button>
                                <button type="button" class="btn-ghost px-2 py-1 text-xs text-brick-500" @click="handleReject(adjustment)">
                                    Reject
                                </button>
                            </template>
                        </td>
                    </tr>
                </tbody>
            </table>

            <BasePagination v-if="items.length" :current-page="meta.currentPage" :last-page="meta.lastPage" :total="meta.total" @change="goToPage" />
        </div>

        <StockAdjustmentForm v-if="formOpen" @close="formOpen = false" @saved="handleSaved" />
    </div>
</template>
