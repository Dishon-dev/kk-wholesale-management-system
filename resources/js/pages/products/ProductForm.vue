<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useForm } from '@/composables/useForm';
import { useToast } from '@/composables/useToast';
import { required, isPositiveNumber, isNonNegativeNumber } from '@/utils/validators';
import * as productsService from '@/services/products.service';
import * as categoriesService from '@/services/categories.service';
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue';

const props = defineProps({
    id: { type: [String, Number], default: null },
});

const router = useRouter();
const toast = useToast();

const isEditing = computed(() => Boolean(props.id));
const loading = ref(isEditing.value);
const categoryOptions = ref([]);

function blankVariant() {
    return { id: null, name: 'Default Variant', sku: '', barcode: '', cost_price: '', selling_price: '', status: true };
}

const form = useForm(
    {
        name: '',
        sku_prefix: '',
        brand: '',
        category_id: '',
        description: '',
        has_variants: false,
        status: true,
        variants: [blankVariant()],
    },
    { rules: { name: [required], sku_prefix: [required] } }
);

function flattenCategories(nodes, depth = 0, out = []) {
    for (const node of nodes) {
        out.push({ id: node.id, label: `${'—'.repeat(depth)} ${node.name}`.trim() });
        if (node.children?.length) flattenCategories(node.children, depth + 1, out);
    }
    return out;
}

onMounted(async () => {
    const { data } = await categoriesService.tree();
    categoryOptions.value = flattenCategories(data);

    if (isEditing.value) {
        try {
            const { data: product } = await productsService.get(props.id);
            form.reset({
                name: product.name,
                sku_prefix: product.sku_prefix,
                brand: product.brand ?? '',
                category_id: product.category_id ?? '',
                description: product.description ?? '',
                has_variants: product.has_variants,
                status: product.status,
                variants: product.variants?.length ? product.variants : [blankVariant()],
            });
        } finally {
            loading.value = false;
        }
    }
});

function addVariant() {
    form.data.variants.push(blankVariant());
}

function removeVariant(index) {
    form.data.variants.splice(index, 1);
}

function onHasVariantsToggle() {
    if (!form.data.has_variants) {
        // Collapse back down to a single Default Variant row.
        form.data.variants = [form.data.variants[0] ?? blankVariant()];
        form.data.variants[0].name = 'Default Variant';
    } else if (form.data.variants.length === 1 && form.data.variants[0].name === 'Default Variant') {
        form.data.variants[0].name = '';
    }
}

async function handleSubmit() {
    // Light client-side pass on variant rows before hitting the network —
    // the backend re-validates all of this regardless.
    const variantErrors = form.data.variants.some(
        (variant) => required(variant.name) || required(variant.sku) || isPositiveNumber(variant.selling_price) || isNonNegativeNumber(variant.cost_price)
    );
    if (variantErrors) {
        toast.error('Check that every variant has a name, SKU and valid prices.');
        return;
    }

    const { ok } = await form.submit((payload) =>
        isEditing.value ? productsService.update(props.id, payload) : productsService.create(payload)
    );

    if (ok) {
        toast.success(isEditing.value ? 'Product updated.' : 'Product created.');
        router.push({ name: 'products.index' });
    }
}
</script>

<template>
    <div class="mx-auto max-w-3xl">
        <div class="mb-5">
            <h1 class="text-xl font-semibold">{{ isEditing ? 'Edit product' : 'Add product' }}</h1>
            <p class="text-sm text-ink-soft">Every product needs at least one variant — plain products get a single "Default Variant".</p>
        </div>

        <LoadingSpinner v-if="loading" label="Loading product" />

        <form v-else class="space-y-6" @submit.prevent="handleSubmit">
            <div class="panel space-y-4 p-5">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="field-label" for="p-name">Product name</label>
                        <input id="p-name" v-model="form.data.name" class="field-input" />
                        <p v-if="form.errors.name" class="field-error">{{ form.errors.name }}</p>
                    </div>
                    <div>
                        <label class="field-label" for="p-sku-prefix">SKU prefix</label>
                        <input id="p-sku-prefix" v-model="form.data.sku_prefix" class="field-input" placeholder="e.g. RC-SUGAR" />
                        <p v-if="form.errors.sku_prefix" class="field-error">{{ form.errors.sku_prefix }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="field-label" for="p-brand">Brand</label>
                        <input id="p-brand" v-model="form.data.brand" class="field-input" />
                    </div>
                    <div>
                        <label class="field-label" for="p-category">Category</label>
                        <select id="p-category" v-model="form.data.category_id" class="field-input">
                            <option value="">No category</option>
                            <option v-for="option in categoryOptions" :key="option.id" :value="option.id">{{ option.label }}</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="field-label" for="p-description">Description</label>
                    <textarea id="p-description" v-model="form.data.description" rows="3" class="field-input"></textarea>
                </div>

                <div class="flex items-center gap-6">
                    <label class="flex items-center gap-2 text-sm text-ink-soft">
                        <input v-model="form.data.has_variants" type="checkbox" class="rounded border-border" @change="onHasVariantsToggle" />
                        This product has multiple variants (size, colour, pack, etc.)
                    </label>
                    <label class="flex items-center gap-2 text-sm text-ink-soft">
                        <input v-model="form.data.status" type="checkbox" class="rounded border-border" />
                        Active
                    </label>
                </div>
            </div>

            <div class="panel p-5">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-ink">{{ form.data.has_variants ? 'Variants' : 'Pricing' }}</h2>
                    <button v-if="form.data.has_variants" type="button" class="btn-secondary text-xs" @click="addVariant">
                        Add variant
                    </button>
                </div>

                <div class="space-y-3">
                    <div
                        v-for="(variant, index) in form.data.variants"
                        :key="index"
                        class="grid grid-cols-12 items-start gap-3 rounded border border-border p-3"
                    >
                        <div class="col-span-3">
                            <label class="field-label">Variant name</label>
                            <input v-model="variant.name" class="field-input" :disabled="!form.data.has_variants" />
                        </div>
                        <div class="col-span-2">
                            <label class="field-label">SKU</label>
                            <input v-model="variant.sku" class="field-input" />
                        </div>
                        <div class="col-span-2">
                            <label class="field-label">Barcode</label>
                            <input v-model="variant.barcode" class="field-input" />
                        </div>
                        <div class="col-span-2">
                            <label class="field-label">Cost price</label>
                            <input v-model="variant.cost_price" type="number" min="0" step="0.01" class="field-input" />
                        </div>
                        <div class="col-span-2">
                            <label class="field-label">Selling price</label>
                            <input v-model="variant.selling_price" type="number" min="0" step="0.01" class="field-input" />
                        </div>
                        <div class="col-span-1 flex h-full items-end justify-end pb-2">
                            <button
                                v-if="form.data.has_variants && form.data.variants.length > 1"
                                type="button"
                                class="text-xs text-brick-500"
                                @click="removeVariant(index)"
                            >
                                Remove
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" class="btn-secondary" @click="router.push({ name: 'products.index' })">Cancel</button>
                <button type="submit" class="btn-primary" :disabled="form.processing">
                    {{ form.processing ? 'Saving…' : 'Save product' }}
                </button>
            </div>
        </form>
    </div>
</template>
