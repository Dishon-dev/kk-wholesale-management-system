<script setup>
import { onMounted } from 'vue';
import { usePagination } from '@/composables/usePagination';
import * as activityService from '@/services/activityLogs.service';
import { formatDateTime } from '@/utils/formatters';
import TableToolbar from '@/components/ui/TableToolbar.vue';
import BasePagination from '@/components/ui/BasePagination.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue';

const { items, loading, params, meta, load, reload, goToPage, setFilter } = usePagination(activityService.list, {
    perPage: 30,
    extraParams: { date_from: '', date_to: '' },
});

onMounted(load);
</script>

<template>
    <div>
        <div class="mb-5">
            <h1 class="text-xl font-semibold">Activity log</h1>
            <p class="text-sm text-ink-soft">Who did what, and when — an audit trail for every meaningful action in the system.</p>
        </div>

        <div class="panel">
            <TableToolbar v-model="params.search" placeholder="Search by user or action…" @update:modelValue="reload">
                <template #filters>
                    <input type="date" class="field-input w-auto" @change="setFilter('date_from', $event.target.value)" />
                    <input type="date" class="field-input w-auto" @change="setFilter('date_to', $event.target.value)" />
                </template>
            </TableToolbar>

            <LoadingSpinner v-if="loading" class="px-5 py-8" label="Loading activity" />

            <EmptyState v-else-if="!items.length" title="No activity recorded" />

            <table v-else class="data-table">
                <thead>
                    <tr>
                        <th>When</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>IP address</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="entry in items" :key="entry.id">
                        <td class="text-ink-soft">{{ formatDateTime(entry.created_at) }}</td>
                        <td class="font-medium">{{ entry.user?.name ?? 'System' }}</td>
                        <td class="text-ink-soft">{{ entry.description }}</td>
                        <td class="figures text-ink-faint">{{ entry.ip_address }}</td>
                    </tr>
                </tbody>
            </table>

            <BasePagination v-if="items.length" :current-page="meta.currentPage" :last-page="meta.lastPage" :total="meta.total" @change="goToPage" />
        </div>
    </div>
</template>
