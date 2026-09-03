<script setup>
import { onMounted, ref } from 'vue';
import { usePermissions } from '@/composables/usePermissions';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import * as categoriesService from '@/services/categories.service';
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import CategoryNode from './CategoryNode.vue';
import CategoryForm from './CategoryForm.vue';

const { can } = usePermissions();
const toast = useToast();
const confirm = useConfirm();

const categories = ref([]);
const loading = ref(true);

async function load() {
    loading.value = true;
    try {
        const { data } = await categoriesService.tree();
        categories.value = data;
    } finally {
        loading.value = false;
    }
}

onMounted(load);

const formOpen = ref(false);
const editingCategory = ref(null);
const parentCategory = ref(null);

function openCreate() {
    editingCategory.value = null;
    parentCategory.value = null;
    formOpen.value = true;
}

function openAddChild(category) {
    editingCategory.value = null;
    parentCategory.value = category;
    formOpen.value = true;
}

function openEdit(category) {
    editingCategory.value = category;
    parentCategory.value = null;
    formOpen.value = true;
}

function handleSaved() {
    formOpen.value = false;
    toast.success('Category saved.');
    load();
}

async function handleDelete(category) {
    const ok = await confirm({
        title: 'Delete this category?',
        message: `"${category.name}" will be removed. Products in it will need to be recategorised.`,
        confirmLabel: 'Delete category',
    });
    if (!ok) return;

    try {
        await categoriesService.remove(category.id);
        toast.success('Category deleted.');
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
                <h1 class="text-xl font-semibold">Categories</h1>
                <p class="text-sm text-ink-soft">Group products so they're easier to browse and report on.</p>
            </div>
            <button v-if="can('categories.create')" type="button" class="btn-primary" @click="openCreate">
                Add top-level category
            </button>
        </div>

        <div class="panel p-3">
            <LoadingSpinner v-if="loading" class="px-2 py-8" label="Loading categories" />
            <EmptyState v-else-if="!categories.length" title="No categories yet" message="Create one to start organising your product catalog." />
            <ul v-else>
                <CategoryNode
                    v-for="category in categories"
                    :key="category.id"
                    :category="category"
                    @add-child="openAddChild"
                    @edit="openEdit"
                    @delete="handleDelete"
                />
            </ul>
        </div>

        <CategoryForm
            v-if="formOpen"
            :category="editingCategory"
            :parent="parentCategory"
            @close="formOpen = false"
            @saved="handleSaved"
        />
    </div>
</template>
