<script setup>
import { onMounted, ref, watch } from 'vue';
import BaseModal from '@/components/ui/BaseModal.vue';
import { useForm } from '@/composables/useForm';
import { required, isEmail } from '@/utils/validators';
import * as usersService from '@/services/users.service';
import * as rolesService from '@/services/roles.service';
import * as branchesService from '@/services/branches.service';
import * as storesService from '@/services/stores.service';

const props = defineProps({
    user: { type: Object, default: null },
});

const emit = defineEmits(['close', 'saved']);

const roles = ref([]);
const branches = ref([]);
const stores = ref([]);

const form = useForm(
    {
        name: props.user?.name ?? '',
        email: props.user?.email ?? '',
        phone: props.user?.phone ?? '',
        branch_id: props.user?.branch_id ?? '',
        store_id: props.user?.store_id ?? '',
        roles: props.user?.roles ?? [],
        password: '',
    },
    { rules: { name: [required], email: [required, isEmail] } }
);

watch(
    () => props.user,
    (user) =>
        form.reset({
            name: user?.name ?? '',
            email: user?.email ?? '',
            phone: user?.phone ?? '',
            branch_id: user?.branch_id ?? '',
            store_id: user?.store_id ?? '',
            roles: user?.roles ?? [],
            password: '',
        })
);

onMounted(async () => {
    const [rolesRes, branchesRes] = await Promise.all([
        rolesService.list(),
        branchesService.list({ per_page: 100, status: 1 }),
    ]);
    roles.value = rolesRes.data;
    branches.value = branchesRes.data;
});

watch(
    () => form.data.branch_id,
    async (branchId) => {
        if (!branchId) {
            stores.value = [];
            return;
        }
        const { data } = await storesService.list({ branch_id: branchId, per_page: 100, status: 1 });
        stores.value = data;
    },
    { immediate: true }
);

function toggleRole(roleName) {
    const index = form.data.roles.indexOf(roleName);
    if (index === -1) form.data.roles.push(roleName);
    else form.data.roles.splice(index, 1);
}

async function handleSubmit() {
    if (!props.user && !form.data.password) {
        form.errors.password = 'A temporary password is required for new users.';
        return;
    }

    const payload = { ...form.data };
    if (props.user && !payload.password) delete payload.password;

    const { ok } = await form.submit(() =>
        props.user ? usersService.update(props.user.id, payload) : usersService.create(payload)
    );
    if (ok) emit('saved');
}
</script>

<template>
    <BaseModal :title="user ? 'Edit user' : 'Add user'" @close="emit('close')">
        <form class="space-y-4" @submit.prevent="handleSubmit">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="field-label" for="u-name">Full name</label>
                    <input id="u-name" v-model="form.data.name" class="field-input" />
                    <p v-if="form.errors.name" class="field-error">{{ form.errors.name }}</p>
                </div>
                <div>
                    <label class="field-label" for="u-email">Email</label>
                    <input id="u-email" v-model="form.data.email" type="email" class="field-input" />
                    <p v-if="form.errors.email" class="field-error">{{ form.errors.email }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="field-label" for="u-phone">Phone</label>
                    <input id="u-phone" v-model="form.data.phone" class="field-input" />
                </div>
                <div>
                    <label class="field-label" for="u-password">
                        {{ user ? 'New password (optional)' : 'Temporary password' }}
                    </label>
                    <input id="u-password" v-model="form.data.password" type="password" class="field-input" autocomplete="new-password" />
                    <p v-if="form.errors.password" class="field-error">{{ form.errors.password }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="field-label" for="u-branch">Branch</label>
                    <select id="u-branch" v-model="form.data.branch_id" class="field-input">
                        <option value="">No branch (all-access)</option>
                        <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
                    </select>
                </div>
                <div>
                    <label class="field-label" for="u-store">Store</label>
                    <select id="u-store" v-model="form.data.store_id" class="field-input" :disabled="!form.data.branch_id">
                        <option value="">All stores in branch</option>
                        <option v-for="store in stores" :key="store.id" :value="store.id">{{ store.name }}</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="field-label">Roles</label>
                <div class="flex flex-wrap gap-3">
                    <label v-for="role in roles" :key="role.id" class="flex items-center gap-1.5 text-sm text-ink-soft">
                        <input
                            type="checkbox"
                            class="rounded border-border"
                            :checked="form.data.roles.includes(role.name)"
                            @change="toggleRole(role.name)"
                        />
                        {{ role.name }}
                    </label>
                </div>
                <p v-if="form.errors.roles" class="field-error">{{ form.errors.roles }}</p>
            </div>
        </form>

        <template #footer>
            <button type="button" class="btn-secondary" @click="emit('close')">Cancel</button>
            <button type="button" class="btn-primary" :disabled="form.processing" @click="handleSubmit">
                {{ form.processing ? 'Saving…' : 'Save user' }}
            </button>
        </template>
    </BaseModal>
</template>
