<script setup>
import { onMounted } from 'vue';
import { usePagination } from '@/composables/usePagination';
import { usePermissions } from '@/composables/usePermissions';
import * as transfersService from '@/services/transfers.service';
import { formatDateTime } from '@/utils/formatters';
import { TRANSFER_STATUSES } from '@/utils/constants';
import TableToolbar from '@/components/ui/TableToolbar.vue';
import BasePagination from '@/components/ui/BasePagination.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue';
import StatusTag from '@/components/ui/StatusTag.vue';

const { can } = usePermissions();

const { items, loading, params, meta, load, reload, goToPage, setFilter } = usePagination(transfersService.list, {
    extraParams: { status: '' },
});

onMounted(load);
</script>

<template>
    <div>
        <div class="mb-5 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold">Stock transfers</h1>
                <p class="text-sm text-ink-soft">Move stock between stores when one is short and another has excess.</p>
            </div>
            <RouterLink v-if="can('transfers.create')" :to="{ name: 'transfers.create' }" class="btn-primary">
                New transfer
            </RouterLink>
        </div>

        <div class="panel">
            <TableToolbar v-model="params.search" placeholder="Search by reference number…" @update:modelValue="reload">
                <template #filters>
                    <select class="field-input w-auto" @change="setFilter('status', $event.target.value)">
                        <option value="">All statuses</option>
                        <option v-for="(meta, key) in TRANSFER_STATUSES" :key="key" :value="key">{{ meta.label }}</option>
                    </select>
                </template>
            </TableToolbar>

            <LoadingSpinner v-if="loading" class="px-5 py-8" label="Loading transfers" />

            <EmptyState v-else-if="!items.length" title="No transfers yet" message="Once a store needs stock from another, create a transfer to track it." />

            <table v-else class="data-table">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Items</th>
                        <th>Requested</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="transfer in items" :key="transfer.id" class="cursor-pointer" @click="$router.push({ name: 'transfers.show', params: { id: transfer.id } })">
                        <td class="figures font-medium">{{ transfer.reference_no }}</td>
                        <td>{{ transfer.from_store?.name }}</td>
                        <td>{{ transfer.to_store?.name }}</td>
                        <td class="text-ink-soft">{{ transfer.items_count ?? transfer.items?.length ?? 0 }}</td>
                        <td class="text-ink-soft">{{ formatDateTime(transfer.created_at) }}</td>
                        <td>
                            <StatusTag :label="TRANSFER_STATUSES[transfer.status]?.label ?? transfer.status" :tone="TRANSFER_STATUSES[transfer.status]?.tone" />
                        </td>
                    </tr>
                </tbody>
            </table>

            <BasePagination v-if="items.length" :current-page="meta.currentPage" :last-page="meta.lastPage" :total="meta.total" @change="goToPage" />
        </div>
    </div>
</template>
