<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import BaseModal from '@/components/ui/BaseModal.vue';
import { useForm } from '@/composables/useForm';
import { required } from '@/utils/validators';
import * as rolesService from '@/services/roles.service';

const props = defineProps({
    role: { type: Object, default: null },
});

const emit = defineEmits(['close', 'saved']);

const allPermissions = ref([]);
const newPermissionName = ref('');
const addingPermission = ref(false);

const form = useForm(
    { name: props.role?.name ?? '', permissions: props.role?.permissions ?? [] },
    { rules: { name: [required] } }
);

watch(
    () => props.role,
    (role) => form.reset({ name: role?.name ?? '', permissions: role?.permissions ?? [] })
);

async function loadPermissions() {
    const { data } = await rolesService.permissions();
    allPermissions.value = data;
}

onMounted(loadPermissions);

const groupedPermissions = computed(() => {
    const groups = {};
    for (const permission of allPermissions.value) {
        const [module] = permission.name.split('.');
        groups[module] ??= [];
        groups[module].push(permission);
    }
    return groups;
});

function togglePermission(name) {
    const index = form.data.permissions.indexOf(name);
    if (index === -1) form.data.permissions.push(name);
    else form.data.permissions.splice(index, 1);
}

async function handleAddPermission() {
    if (!newPermissionName.value.trim()) return;
    addingPermission.value = true;
    try {
        const { data } = await rolesService.createPermission({ name: newPermissionName.value.trim() });
        allPermissions.value.push(data);
        form.data.permissions.push(data.name);
        newPermissionName.value = '';
    } finally {
        addingPermission.value = false;
    }
}

async function handleSubmit() {
    const { ok } = await form.submit(() =>
        props.role ? rolesService.update(props.role.id, form.data) : rolesService.create(form.data)
    );
    if (ok) emit('saved');
}
</script>

<template>
    <BaseModal :title="role ? 'Edit role' : 'Add role'" size="lg" @close="emit('close')">
        <form class="space-y-4" @submit.prevent="handleSubmit">
            <div>
                <label class="field-label" for="role-name">Role name</label>
                <input id="role-name" v-model="form.data.name" class="field-input" placeholder="e.g. Warehouse Supervisor" :disabled="role?.is_system" />
                <p v-if="form.errors.name" class="field-error">{{ form.errors.name }}</p>
                <p v-if="role?.is_system" class="mt-1 text-xs text-ink-faint">Built-in role names can't be changed, but their permissions can.</p>
            </div>

            <div>
                <label class="field-label">Permissions</label>
                <div class="max-h-72 space-y-4 overflow-y-auto rounded border border-border p-3">
                    <div v-for="(permissions, module) in groupedPermissions" :key="module">
                        <p class="mb-1.5 text-xs font-semibold capitalize text-ink">{{ module }}</p>
                        <div class="grid grid-cols-2 gap-1.5">
                            <label v-for="permission in permissions" :key="permission.id" class="flex items-center gap-1.5 text-sm text-ink-soft">
                                <input
                                    type="checkbox"
                                    class="rounded border-border"
                                    :checked="form.data.permissions.includes(permission.name)"
                                    @change="togglePermission(permission.name)"
                                />
                                {{ permission.name }}
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-end gap-2">
                <div class="flex-1">
                    <label class="field-label" for="new-permission">Add a new permission</label>
                    <input
                        id="new-permission"
                        v-model="newPermissionName"
                        class="field-input"
                        placeholder="e.g. reports.export"
                    />
                </div>
                <button type="button" class="btn-secondary" :disabled="addingPermission" @click="handleAddPermission">
                    {{ addingPermission ? 'Adding…' : 'Add' }}
                </button>
            </div>
        </form>

        <template #footer>
            <button type="button" class="btn-secondary" @click="emit('close')">Cancel</button>
            <button type="button" class="btn-primary" :disabled="form.processing" @click="handleSubmit">
                {{ form.processing ? 'Saving…' : 'Save role' }}
            </button>
        </template>
    </BaseModal>
</template>
