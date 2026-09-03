<script setup>
import { onMounted, ref } from 'vue';
import { usePagination } from '@/composables/usePagination';
import { useAuthStore } from '@/stores/auth';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import * as usersService from '@/services/users.service';
import { formatDateTime, initials } from '@/utils/formatters';
import TableToolbar from '@/components/ui/TableToolbar.vue';
import BasePagination from '@/components/ui/BasePagination.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue';
import StatusTag from '@/components/ui/StatusTag.vue';
import UserForm from './UserForm.vue';

const auth = useAuthStore();
const toast = useToast();
const confirm = useConfirm();

const { items, loading, params, meta, load, reload, goToPage } = usePagination(usersService.list);

onMounted(load);

const formOpen = ref(false);
const editingUser = ref(null);

function openCreate() {
    editingUser.value = null;
    formOpen.value = true;
}

function openEdit(user) {
    editingUser.value = user;
    formOpen.value = true;
}

function handleSaved() {
    formOpen.value = false;
    toast.success(editingUser.value ? 'User updated.' : 'User created.');
    load();
}

async function toggleActive(user) {
    try {
        await usersService.setActive(user.id, !user.is_active);
        toast.success(user.is_active ? 'User deactivated.' : 'User activated.');
        load();
    } catch (error) {
        toast.error(error.message);
    }
}

async function handleResetPassword(user) {
    const ok = await confirm({
        title: 'Reset this user\u2019s password?',
        message: `A new temporary password will be generated for ${user.name} and emailed to them.`,
        confirmLabel: 'Reset password',
        tone: 'primary',
    });
    if (!ok) return;

    try {
        await usersService.resetPassword(user.id);
        toast.success('Password reset. The user has been notified by email.');
    } catch (error) {
        toast.error(error.message);
    }
}

async function handleDelete(user) {
    const ok = await confirm({
        title: 'Delete this user?',
        message: `${user.name} will lose access immediately. Their past actions remain in the activity log.`,
        confirmLabel: 'Delete user',
    });
    if (!ok) return;

    try {
        await usersService.remove(user.id);
        toast.success('User deleted.');
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
                <h1 class="text-xl font-semibold">Users</h1>
                <p class="text-sm text-ink-soft">Everyone with access to the system, and what they can see.</p>
            </div>
            <button type="button" class="btn-primary" @click="openCreate">Add user</button>
        </div>

        <div class="panel">
            <TableToolbar v-model="params.search" placeholder="Search by name or email…" @update:modelValue="reload" />

            <LoadingSpinner v-if="loading" class="px-5 py-8" label="Loading users" />

            <EmptyState v-else-if="!items.length" title="No users yet" />

            <table v-else class="data-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Roles</th>
                        <th>Scope</th>
                        <th>Last login</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in items" :key="user.id">
                        <td>
                            <div class="flex items-center gap-2.5">
                                <span
                                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-brand-100 text-xs font-semibold text-brand-700">
                                    {{ initials(user.name) }}
                                </span>
                                <div>
                                    <p class="font-medium text-ink">{{ user.name }}</p>
                                    <p class="text-xs text-ink-faint">{{ user.email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="text-ink-soft">{{ user.roles?.[0].name || '' }}</td>
                        <td class="text-ink-soft">{{ user.store?.name ?? user.branch?.name ?? 'All branches' }}</td>
                        <td class="text-ink-soft">{{ user.last_login_at ? formatDateTime(user.last_login_at) : 'Never'
                            }}</td>
                        <td>
                            <StatusTag :label="user.is_active ? 'Active' : 'Inactive'"
                                :tone="user.is_active ? 'positive' : 'neutral'" />
                        </td>
                        <td>
                            <button type="button" class="btn-ghost px-2 py-1 text-xs"
                                @click="openEdit(user)">Edit</button>
                            <button type="button" class="btn-ghost px-2 py-1 text-xs"
                                @click="handleResetPassword(user)">Reset password</button>
                            <button type="button" class="btn-ghost px-2 py-1 text-xs" @click="toggleActive(user)">
                                {{ user.is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                            <button v-if="user.id !== auth.user?.id" type="button"
                                class="btn-ghost px-2 py-1 text-xs text-brick-500" @click="handleDelete(user)">
                                Delete
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <BasePagination v-if="items.length" :current-page="meta.currentPage" :last-page="meta.lastPage"
                :total="meta.total" @change="goToPage" />
        </div>

        <UserForm v-if="formOpen" :user="editingUser" @close="formOpen = false" @saved="handleSaved" />
    </div>
</template>
