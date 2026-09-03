<script setup>
import { onMounted, ref } from 'vue';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import * as rolesService from '@/services/roles.service';
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import RoleForm from './RoleForm.vue';

const toast = useToast();
const confirm = useConfirm();

const roles = ref([]);
const loading = ref(true);

async function load() {
    loading.value = true;
    try {
        const { data } = await rolesService.list();
        roles.value = data;
    } finally {
        loading.value = false;
    }
}

onMounted(load);

const formOpen = ref(false);
const editingRole = ref(null);

function openCreate() {
    editingRole.value = null;
    formOpen.value = true;
}

function openEdit(role) {
    editingRole.value = role;
    formOpen.value = true;
}

function handleSaved() {
    formOpen.value = false;
    toast.success(editingRole.value ? 'Role updated.' : 'Role created.');
    load();
}

async function handleDelete(role) {
    const ok = await confirm({
        title: 'Delete this role?',
        message: `Anyone assigned "${role.name}" will lose the permissions it grants.`,
        confirmLabel: 'Delete role',
    });
    if (!ok) return;

    try {
        await rolesService.remove(role.id);
        toast.success('Role deleted.');
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
                <h1 class="text-xl font-semibold">Roles & permissions</h1>
                <p class="text-sm text-ink-soft">
                    Administrator, Branch Manager and Store Manager are set up out of the box — add more roles here as the business needs them.
                </p>
            </div>
            <button type="button" class="btn-primary" @click="openCreate">Add role</button>
        </div>

        <LoadingSpinner v-if="loading" label="Loading roles" />

        <EmptyState v-else-if="!roles.length" title="No roles defined" />

        <div v-else class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            <div v-for="role in roles" :key="role.id" class="panel p-5">
                <div class="mb-2 flex items-start justify-between">
                    <div>
                        <p class="font-display text-sm font-semibold text-ink">{{ role.name }}</p>
                        <p class="text-xs text-ink-faint">{{ role.users_count ?? 0 }} user{{ role.users_count === 1 ? '' : 's' }}</p>
                    </div>
                    <span v-if="role.is_system" class="tag-info">Built-in</span>
                </div>

                <p class="mb-4 text-xs text-ink-soft">{{ role.permissions?.length ?? 0 }} permissions granted</p>

                <div class="flex gap-2">
                    <button type="button" class="btn-secondary text-xs" @click="openEdit(role)">Edit</button>
                    <button
                        v-if="!role.is_system"
                        type="button"
                        class="btn-ghost text-xs text-brick-500"
                        @click="handleDelete(role)"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>

        <RoleForm v-if="formOpen" :role="editingRole" @close="formOpen = false" @saved="handleSaved" />
    </div>
</template>
