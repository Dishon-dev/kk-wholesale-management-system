<script setup>
import { onMounted, ref } from 'vue';
import { usePagination } from '@/composables/usePagination';
import { usePermissions } from '@/composables/usePermissions';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import { PERMISSIONS } from '@/utils/constants';
import * as branchesService from '@/services/branches.service';

import TableToolbar from '@/components/ui/TableToolbar.vue';
import BasePagination from '@/components/ui/BasePagination.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue';
import StatusTag from '@/components/ui/StatusTag.vue';
import BranchForm from './BranchForm.vue';

const { can } = usePermissions();
const toast = useToast();
const confirm = useConfirm();

const {
    items,
    loading,
    params,
    meta,
    load,
    reload,
    goToPage,
} = usePagination(branchesService.list);

const formOpen = ref(false);
const editingBranch = ref(null);

onMounted(load);

function openCreate() {
    editingBranch.value = null;
    formOpen.value = true;
}

function openEdit(branch) {
    editingBranch.value = branch;
    formOpen.value = true;
}

function handleSaved() {
    const wasEditing = !!editingBranch.value;

    formOpen.value = false;
    editingBranch.value = null;

    toast.success(
        wasEditing
            ? 'Branch updated successfully.'
            : 'Branch created successfully.'
    );

    load();
}

async function handleDelete(branch) {
    const ok = await confirm({
        title: 'Delete this branch?',
        message: `"${branch.name}" will be archived. Stores under it must be moved or removed first.`,
        confirmLabel: 'Delete branch',
    });

    if (!ok) {
        return;
    }

    try {
        await branchesService.remove(branch.id);

        toast.success('Branch deleted successfully.');

        await load();
    } catch (error) {
        toast.error(
            error?.message ?? 'Unable to delete the branch.'
        );
    }
}
</script>

<template>
    <div>
        <div class="mb-5 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold">
                    Branches
                </h1>

                <p class="text-sm text-ink-soft">
                    The top-level regions your stores belong to.
                </p>
            </div>

            <button
                v-if="can(PERMISSIONS.BRANCHES_CREATE)"
                type="button"
                class="btn-primary"
                @click="openCreate"
            >
                Add branch
            </button>
        </div>

        <!-- Table -->
        <div class="panel">
            <TableToolbar
                v-model="params.search"
                placeholder="Search branches…"
                @update:modelValue="reload"
            />

            <LoadingSpinner
                v-if="loading"
                class="px-5 py-8"
                label="Loading branches"
            />

            <EmptyState
                v-else-if="!items.length"
                title="No branches yet"
                message="Add your first branch to start setting up stores under it."
            />

            <table
                v-else
                class="data-table"
            >
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Email</th>
                        <th>phone</th>
                        <th>Stores</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <tr
                        v-for="branch in items"
                        :key="branch.id"
                    >
                        <td class="font-medium">
                            {{ branch.name }}
                        </td>

                        <td class="figures text-ink-soft">
                            {{ branch.branch_code }}
                        </td>

                        <td class="text-ink-soft">
                            {{ branch.email || '—' }}
                        </td>

                        <td class="text-ink-soft">
                            {{ branch.phone || '—' }}
                        </td>

                        <td class="text-ink-soft">
                            {{ branch.stores_count ?? 0 }}
                        </td>

                        <td>
                            <StatusTag
                                :label="branch.is_active ? 'Active' : 'Inactive'"
                                :tone="branch.is_active ? 'positive' : 'neutral'"
                            />
                        </td>

                        <td>
                            <button
                                v-if="can(PERMISSIONS.BRANCHES_UPDATE)"
                                type="button"
                                class="btn-ghost px-2 py-1 text-xs"
                                @click="openEdit(branch)"
                            >
                                Edit
                            </button>

                            <button
                                v-if="can(PERMISSIONS.BRANCHES_DELETE)"
                                type="button"
                                class="btn-ghost px-2 py-1 text-xs text-brick-500"
                                @click="handleDelete(branch)"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <BasePagination
                v-if="items.length"
                :current-page="meta.currentPage"
                :last-page="meta.lastPage"
                :total="meta.total"
                @change="goToPage"
            />
        </div>

        <BranchForm
            v-if="formOpen"
            :branch="editingBranch"
            @close="formOpen = false"
            @saved="handleSaved"
        />
    </div>
</template>
