<script setup>
import { onMounted, ref } from 'vue';
import { usePagination } from '@/composables/usePagination';
import { usePermissions } from '@/composables/usePermissions';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import * as storesService from '@/services/stores.service';
import TableToolbar from '@/components/ui/TableToolbar.vue';
import BasePagination from '@/components/ui/BasePagination.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue';
import StatusTag from '@/components/ui/StatusTag.vue';
import StoreForm from './StoreForm.vue';

const { can } = usePermissions();
const toast = useToast();
const confirm = useConfirm();

const { items, loading, params, meta, load, reload, goToPage } = usePagination(storesService.list);

onMounted(load);

const formOpen = ref(false);
const editingStore = ref(null);

function openCreate() {
    editingStore.value = null;
    formOpen.value = true;
}

function openEdit(store) {
    editingStore.value = store;
    formOpen.value = true;
}

function handleSaved() {
    formOpen.value = false;
    toast.success(editingStore.value ? 'Store updated.' : 'Store created.');
    load();
}

async function handleDelete(store) {
    const ok = await confirm({
        title: 'Delete this store?',
        message: `"${store.name}" will be archived. It must have no stock on hand first.`,
        confirmLabel: 'Delete store',
    });
    if (!ok) return;

    try {
        await storesService.remove(store.id);
        toast.success('Store deleted.');
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
                <h1 class="text-xl font-semibold">Stores</h1>
                <p class="text-sm text-ink-soft">Each store keeps its own stock, sales and transfers.</p>
            </div>
            <button v-if="can('stores.create')" type="button" class="btn-primary" @click="openCreate">
                Add store
            </button>
        </div>

        <div class="panel">
            <TableToolbar v-model="params.search" placeholder="Search stores…" @update:modelValue="reload" />

            <LoadingSpinner v-if="loading" class="px-5 py-8" label="Loading stores" />

            <EmptyState v-else-if="!items.length" title="No stores yet"
                message="Add a store under a branch to start tracking stock there." />

            <table v-else class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Branch</th>
                        <th>Code</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="store in items" :key="store.id">
                        <td class="font-medium">{{ store.name }}</td>
                        <td class="text-ink-soft">{{ store.branch?.name }}</td>
                        <td class="figures text-ink-soft">{{ store.store_code }}</td>
                        <td class="text-ink-soft">{{ store.phone || store.email || '' }}</td>
                        <td>
                            <StatusTag :label="store.is_active ? 'Active' : 'Inactive'"
                                :tone="store.status ? 'positive' : 'neutral'" />
                        </td>
                        <td>
                            <button v-if="can('stores.update')" type="button" class="btn-ghost px-2 py-1 text-xs"
                                @click="openEdit(store)">
                                Edit
                            </button>
                            <button v-if="can('stores.delete')" type="button"
                                class="btn-ghost px-2 py-1 text-xs text-brick-500" @click="handleDelete(store)">
                                Delete
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <BasePagination v-if="items.length" :current-page="meta.currentPage" :last-page="meta.lastPage"
                :total="meta.total" @change="goToPage" />
        </div>

        <StoreForm v-if="formOpen" :store="editingStore" @close="formOpen = false" @saved="handleSaved" />
    </div>
</template>
