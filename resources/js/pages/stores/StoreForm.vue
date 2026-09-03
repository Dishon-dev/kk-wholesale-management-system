<script setup>
import { onMounted, ref, watch } from 'vue';
import BaseModal from '@/components/ui/BaseModal.vue';
import { useForm } from '@/composables/useForm';
import { required } from '@/utils/validators';
import * as storesService from '@/services/stores.service';
import * as branchesService from '@/services/branches.service';

const props = defineProps({
    store: { type: Object, default: null },
});

const emit = defineEmits(['close', 'saved']);

const branches = ref([]);

const form = useForm(
    {
        branch_id: props.store?.branch_id ?? '',
        name: props.store?.name ?? '',
        code: props.store?.code ?? '',
        phone: props.store?.phone ?? '',
        email: props.store?.email ?? '',
        address: props.store?.address ?? '',
        status: props.store?.status ?? true,
    },
    { rules: { name: [required], code: [required], branch_id: [required] } }
);

watch(
    () => props.store,
    (store) => form.reset(store ?? {})
);

onMounted(async () => {
    const { data } = await branchesService.list({ per_page: 100, status: 1 });
    branches.value = data;
});

async function handleSubmit() {
    const { ok } = await form.submit((payload) =>
        props.store ? storesService.update(props.store.id, payload) : storesService.create(payload)
    );
    if (ok) emit('saved');
}
</script>

<template>
    <BaseModal :title="store ? 'Edit store' : 'Add store'" @close="emit('close')">
        <form class="space-y-4" @submit.prevent="handleSubmit">
            <div>
                <label class="field-label" for="branch">Branch</label>
                <select id="branch" v-model="form.data.branch_id" class="field-input">
                    <option value="" disabled>Select a branch</option>
                    <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
                </select>
                <p v-if="form.errors.branch_id" class="field-error">{{ form.errors.branch_id }}</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="field-label" for="name">Store name</label>
                    <input id="name" v-model="form.data.name" class="field-input" placeholder="e.g. Westlands Store" />
                    <p v-if="form.errors.name" class="field-error">{{ form.errors.name }}</p>
                </div>
                <div>
                    <label class="field-label" for="code">Store code</label>
                    <input id="code" v-model="form.data.code" class="field-input" placeholder="e.g. NRB-WL" />
                    <p v-if="form.errors.code" class="field-error">{{ form.errors.code }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="field-label" for="phone">Phone</label>
                    <input id="phone" v-model="form.data.phone" class="field-input" />
                </div>
                <div>
                    <label class="field-label" for="email">Email</label>
                    <input id="email" v-model="form.data.email" type="email" class="field-input" />
                </div>
            </div>

            <div>
                <label class="field-label" for="address">Address</label>
                <textarea id="address" v-model="form.data.address" rows="2" class="field-input"></textarea>
            </div>

            <label class="flex items-center gap-2 text-sm text-ink-soft">
                <input v-model="form.data.status" type="checkbox" class="rounded border-border" />
                Active
            </label>
        </form>

        <template #footer>
            <button type="button" class="btn-secondary" @click="emit('close')">Cancel</button>
            <button type="button" class="btn-primary" :disabled="form.processing" @click="handleSubmit">
                {{ form.processing ? 'Saving…' : 'Save store' }}
            </button>
        </template>
    </BaseModal>
</template>
