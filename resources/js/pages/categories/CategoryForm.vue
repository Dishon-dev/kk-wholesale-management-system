<script setup>
import { watch } from 'vue';
import BaseModal from '@/components/ui/BaseModal.vue';
import { useForm } from '@/composables/useForm';
import { required } from '@/utils/validators';
import * as categoriesService from '@/services/categories.service';

const props = defineProps({
    category: { type: Object, default: null },
    parent: { type: Object, default: null },
});

const emit = defineEmits(['close', 'saved']);

const form = useForm(
    {
        name: props.category?.name ?? '',
        // description: props.category?.description ?? '',
        parent_id: props.category?.parent_id ?? props.parent?.id ?? null,
    },
    { rules: { name: [required] } }
);

watch(
    () => props.category,
    (category) => form.reset({ name: category?.name ?? '', parent_id: category?.parent_id ?? props.parent?.id ?? null })
);

async function handleSubmit() {
    const { ok } = await form.submit((payload) =>
        props.category ? categoriesService.update(props.category.id, payload) : categoriesService.create(payload)
    );
    if (ok) emit('saved');
}
</script>

<template>
    <BaseModal :title="category ? 'Edit category' : 'Add category'" size="sm" @close="emit('close')">
        <form class="space-y-4" @submit.prevent="handleSubmit">
            <p v-if="parent && !category" class="text-xs text-ink-soft">
                Adding under <span class="font-medium text-ink">{{ parent.name }}</span>
            </p>
            <div>
                <label class="field-label" for="category-name">Category name</label>
                <input id="category-name" v-model="form.data.name" class="field-input" />
                <p v-if="form.errors.name" class="field-error">{{ form.errors.name }}</p>
            </div>

            <!-- <div>
                <label class="field-label" for="category-description">Category Description</label>
                <input id="category-description" v-model="form.data.description" class="field-input" />
                <p v-if="form.errors.description" class="field-error">{{ form.errors.description }}</p>
            </div> -->
        </form>

        <template #footer>
            <button type="button" class="btn-secondary" @click="emit('close')">Cancel</button>
            <button type="button" class="btn-primary" :disabled="form.processing" @click="handleSubmit">
                {{ form.processing ? 'Saving…' : 'Save category' }}
            </button>
        </template>
    </BaseModal>
</template>
