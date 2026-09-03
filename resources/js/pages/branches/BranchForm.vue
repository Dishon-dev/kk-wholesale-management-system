<script setup>
import { watch } from 'vue';
import BaseModal from '@/components/ui/BaseModal.vue';
import { useForm } from '@/composables/useForm';
import { required } from '@/utils/validators';
import * as branchesService from '@/services/branches.service';

const props = defineProps({
    branch: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits([
    'close',
    'saved',
]);

const form = useForm(
    {
        name: '',
        code: '',
        phone: '',
        email: '',
        address: '',
        status: true,
    },
    {
        rules: {
            name: [required],
            code: [required],
        },
    }
);

function resetForm(branch = null) {
    form.reset({
        name: branch?.name ?? '',
        code: branch?.code ?? '',
        phone: branch?.phone ?? '',
        email: branch?.email ?? '',
        address: branch?.address ?? '',
        status: branch?.status ?? true,
    });
}

watch(
    () => props.branch,
    (branch) => {
        resetForm(branch);
    },
    {
        immediate: true,
    }
);

async function handleSubmit() {
    const { ok } = await form.submit((payload) => {
        if (props.branch) {
            return branchesService.update(
                props.branch.id,
                payload
            );
        }

        return branchesService.create(payload);
    });

    if (ok) {
        emit('saved');
    }
}
</script>

<template>
    <BaseModal
        :title="branch ? 'Edit branch' : 'Add branch'"
        @close="emit('close')"
    >
        <form
            class="space-y-4"
            @submit.prevent="handleSubmit"
        >
            <!-- Name / Code -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label
                        class="field-label"
                        for="branch-name"
                    >
                        Branch name
                    </label>

                    <input
                        id="branch-name"
                        v-model="form.data.name"
                        type="text"
                        class="field-input"
                        placeholder="e.g. Nairobi Branch"
                    />

                    <p
                        v-if="form.errors.name"
                        class="field-error"
                    >
                        {{ form.errors.name }}
                    </p>
                </div>

                <div>
                    <label
                        class="field-label"
                        for="branch-code"
                    >
                        Branch code
                    </label>

                    <input
                        id="branch-code"
                        v-model="form.data.code"
                        type="text"
                        class="field-input"
                        placeholder="e.g. NRB"
                    />

                    <p
                        v-if="form.errors.code"
                        class="field-error"
                    >
                        {{ form.errors.code }}
                    </p>
                </div>
            </div>

            <!-- Phone / Email -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label
                        class="field-label"
                        for="branch-phone"
                    >
                        Phone
                    </label>

                    <input
                        id="branch-phone"
                        v-model="form.data.phone"
                        type="tel"
                        class="field-input"
                        placeholder="e.g. +254 700 000 000"
                    />

                    <p
                        v-if="form.errors.phone"
                        class="field-error"
                    >
                        {{ form.errors.phone }}
                    </p>
                </div>

                <div>
                    <label
                        class="field-label"
                        for="branch-email"
                    >
                        Email
                    </label>

                    <input
                        id="branch-email"
                        v-model="form.data.email"
                        type="email"
                        class="field-input"
                        placeholder="branch@example.com"
                    />

                    <p
                        v-if="form.errors.email"
                        class="field-error"
                    >
                        {{ form.errors.email }}
                    </p>
                </div>
            </div>

            <!-- Address -->
            <div>
                <label
                    class="field-label"
                    for="branch-address"
                >
                    Address
                </label>

                <textarea
                    id="branch-address"
                    v-model="form.data.address"
                    rows="3"
                    class="field-input"
                    placeholder="Branch physical address"
                ></textarea>

                <p
                    v-if="form.errors.address"
                    class="field-error"
                >
                    {{ form.errors.address }}
                </p>
            </div>

            <!-- Status -->
            <label
                class="flex items-center gap-2 text-sm text-ink-soft"
            >
                <input
                    v-model="form.data.status"
                    type="checkbox"
                    class="rounded border-border"
                />

                <span>Active</span>
            </label>

            <p
                v-if="form.errors.status"
                class="field-error"
            >
                {{ form.errors.status }}
            </p>
        </form>

        <template #footer>
            <button
                type="button"
                class="btn-secondary"
                :disabled="form.processing"
                @click="emit('close')"
            >
                Cancel
            </button>

            <button
                type="button"
                class="btn-primary"
                :disabled="form.processing"
                @click="handleSubmit"
            >
                {{ form.processing ? 'Saving…' : 'Save branch' }}
            </button>
        </template>
    </BaseModal>
</template>
