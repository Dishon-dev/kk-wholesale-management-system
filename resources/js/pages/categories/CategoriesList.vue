<script setup>
import { computed, onMounted, ref } from 'vue';
import { usePermissions } from '@/composables/usePermissions';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import * as categoriesService from '@/services/categories.service';
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import StatusTag from '@/components/ui/StatusTag.vue';
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

const rows = computed(() => {
    const flat = [];
    function walk(nodes, depth) {
        for (const node of nodes ?? []) {
            flat.push({ category: node, depth });
            if (node.children?.length) {
                walk(node.children, depth + 1);
            }
        }
    }
    walk(categories.value, 0);
    return flat;
});

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
        <div class="panel">
            <LoadingSpinner v-if="loading" class="px-5 py-8" label="Loading categories" />
            <EmptyState v-else-if="!rows.length" title="No categories yet"
                message="Create one to start organising your product catalog." />
            <table v-else class="data-table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Slug</th>
                        <th>Products</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in rows" :key="row.category.id">
                        <td>
                            <span class="font-medium text-ink" :style="{ paddingLeft: `${row.depth * 1.25}rem` }">
                                <span v-if="row.depth" class="mr-1 text-ink-faint">↳</span>{{ row.category.name }}
                            </span>
                        </td>
                        <td class="text-ink-soft">{{ row.category.slug || '' }}</td>
                        <td class="text-ink-soft">{{ row.category.products_count ?? 0 }}</td>
                        <td>
                            <StatusTag :label="row.category.is_active ? 'Active' : 'Inactive'"
                                :tone="row.category.is_active ? 'positive' : 'neutral'" />
                        </td>
                        <td>
                            <button v-if="can('categories.create')" type="button"
                                class="btn-ghost inline-flex px-2 py-1 text-xs" @click="openAddChild(row.category)">
                                Add Subcategory
                            </button>
                            <button v-if="can('categories.update')" type="button"
                                class="btn-ghost inline-flex px-2 py-1 text-xs" @click="openEdit(row.category)">
                                Edit
                            </button>
                            <button v-if="can('categories.delete')" type="button"
                                class="btn-ghost px-2 py-1 text-xs text-brick-500" @click="handleDelete(row.category)">
                                Delete
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
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
