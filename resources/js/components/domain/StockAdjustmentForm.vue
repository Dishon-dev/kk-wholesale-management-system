<script setup>
import { ref } from 'vue';
import BaseModal from '@/components/ui/BaseModal.vue';
import VariantPicker from './VariantPicker.vue';
import { useForm } from '@/composables/useForm';
import { required, isPositiveNumber } from '@/utils/validators';
import * as stockService from '@/services/stock.service';
import * as storesService from '@/services/stores.service';
import { onMounted } from 'vue';

const props = defineProps({
    stock: { type: Object, default: null },
});

const emit = defineEmits(['close', 'saved']);

const stores = ref([]);
const selectedVariant = ref(props.stock?.product_variant ?? null);

const form = useForm(
    {
        stock_id: props.stock?.id ?? null,
        store_id: props.stock?.store_id ?? '',
        product_variant_id: props.stock?.product_variant_id ?? '',
        adjustment_type: 'INCREASE',
        quantity: '',
        reason: '',
    },
    { rules: { store_id: [required], product_variant_id: [required], quantity: [required, isPositiveNumber], reason: [required] } }
);

onMounted(async () => {
    if (!props.stock) {
        const { data } = await storesService.list({ per_page: 100, status: 1 });
        stores.value = data;
    }
});

function onVariantSelected(variant) {
    selectedVariant.value = variant;
    form.data.product_variant_id = variant.id;
}

async function handleSubmit() {
    const { ok } = await form.submit((payload) => stockService.createAdjustment(payload));
    if (ok) emit('saved');
}
</script>

<template>
    <BaseModal title="Request stock adjustment" @close="emit('close')">
        <form class="space-y-4" @submit.prevent="handleSubmit">
            <div v-if="stock" class="rounded border border-border bg-canvas px-3 py-2 text-sm">
                <p class="font-medium text-ink">{{ stock.product_variant?.product?.name }} — {{ stock.product_variant?.name }}</p>
                <p class="text-xs text-ink-soft">at {{ stock.store?.name }} · currently {{ stock.quantity }} on hand</p>
            </div>

            <template v-else>
                <div>
                    <label class="field-label" for="adj-store">Store</label>
                    <select id="adj-store" v-model="form.data.store_id" class="field-input">
                        <option value="" disabled>Select a store</option>
                        <option v-for="store in stores" :key="store.id" :value="store.id">{{ store.name }}</option>
                    </select>
                    <p v-if="form.errors.store_id" class="field-error">{{ form.errors.store_id }}</p>
                </div>

                <div>
                    <label class="field-label">Product / variant</label>
                    <VariantPicker :store-id="form.data.store_id" @select="onVariantSelected" />
                    <p v-if="selectedVariant" class="mt-1 text-xs text-moss-700">
                        Selected: {{ selectedVariant.product?.name }} — {{ selectedVariant.name }}
                    </p>
                    <p v-if="form.errors.product_variant_id" class="field-error">{{ form.errors.product_variant_id }}</p>
                </div>
            </template>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="field-label" for="adj-type">Adjustment type</label>
                    <select id="adj-type" v-model="form.data.adjustment_type" class="field-input">
                        <option value="INCREASE">Increase stock</option>
                        <option value="DECREASE">Decrease stock</option>
                    </select>
                </div>
                <div>
                    <label class="field-label" for="adj-quantity">Quantity</label>
                    <input id="adj-quantity" v-model="form.data.quantity" type="number" min="1" class="field-input" />
                    <p v-if="form.errors.quantity" class="field-error">{{ form.errors.quantity }}</p>
                </div>
            </div>

            <div>
                <label class="field-label" for="adj-reason">Reason</label>
                <textarea
                    id="adj-reason"
                    v-model="form.data.reason"
                    rows="2"
                    class="field-input"
                    placeholder="e.g. Damaged in storage, physical count correction…"
                ></textarea>
                <p v-if="form.errors.reason" class="field-error">{{ form.errors.reason }}</p>
            </div>

            <p class="text-xs text-ink-faint">
                Adjustments are recorded as a request. A manager with approval rights must confirm it before stock changes.
            </p>
        </form>

        <template #footer>
            <button type="button" class="btn-secondary" @click="emit('close')">Cancel</button>
            <button type="button" class="btn-primary" :disabled="form.processing" @click="handleSubmit">
                {{ form.processing ? 'Submitting…' : 'Submit for approval' }}
            </button>
        </template>
    </BaseModal>
</template>
