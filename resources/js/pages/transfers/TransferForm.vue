<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useForm } from '@/composables/useForm';
import { useToast } from '@/composables/useToast';
import { required, isPositiveNumber } from '@/utils/validators';
import * as storesService from '@/services/stores.service';
import * as transfersService from '@/services/transfers.service';
import VariantPicker from '@/components/domain/VariantPicker.vue';

const router = useRouter();
const toast = useToast();

const stores = ref([]);

const form = useForm(
    { from_store_id: '', to_store_id: '', remarks: '', items: [] },
    { rules: { from_store_id: [required], to_store_id: [required] } }
);

onMounted(async () => {
    const { data } = await storesService.list({ per_page: 100, status: 1 });
    stores.value = data;
});

function addItem(variant) {
    const alreadyAdded = form.data.items.find((item) => item.product_variant_id === variant.id);
    if (alreadyAdded) {
        toast.info('That variant is already on this transfer.');
        return;
    }
    form.data.items.push({
        product_variant_id: variant.id,
        variant_label: `${variant.product?.name} — ${variant.name}`,
        sku: variant.sku,
        quantity_requested: 1,
    });
}

function removeItem(index) {
    form.data.items.splice(index, 1);
}

async function handleSubmit() {
    if (form.data.from_store_id === form.data.to_store_id) {
        toast.error('The source and destination store must be different.');
        return;
    }
    if (!form.data.items.length) {
        toast.error('Add at least one item to transfer.');
        return;
    }
    if (form.data.items.some((item) => isPositiveNumber(item.quantity_requested))) {
        toast.error('Every item needs a quantity greater than zero.');
        return;
    }

    const { ok } = await form.submit((payload) =>
        transfersService.create({
            ...payload,
            items: payload.items.map(({ product_variant_id, quantity_requested }) => ({ product_variant_id, quantity_requested })),
        })
    );

    if (ok) {
        toast.success('Transfer request created.');
        router.push({ name: 'transfers.index' });
    }
}
</script>

<template>
    <div class="mx-auto max-w-2xl">
        <div class="mb-5">
            <h1 class="text-xl font-semibold">New stock transfer</h1>
            <p class="text-sm text-ink-soft">Request stock be moved from one store to another. Nothing leaves the source store until it's approved and sent.</p>
        </div>

        <form class="space-y-6" @submit.prevent="handleSubmit">
            <div class="panel space-y-4 p-5">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="field-label" for="from-store">From store</label>
                        <select id="from-store" v-model="form.data.from_store_id" class="field-input">
                            <option value="" disabled>Select a store</option>
                            <option v-for="store in stores" :key="store.id" :value="store.id">{{ store.name }}</option>
                        </select>
                        <p v-if="form.errors.from_store_id" class="field-error">{{ form.errors.from_store_id }}</p>
                    </div>
                    <div>
                        <label class="field-label" for="to-store">To store</label>
                        <select id="to-store" v-model="form.data.to_store_id" class="field-input">
                            <option value="" disabled>Select a store</option>
                            <option v-for="store in stores" :key="store.id" :value="store.id">{{ store.name }}</option>
                        </select>
                        <p v-if="form.errors.to_store_id" class="field-error">{{ form.errors.to_store_id }}</p>
                    </div>
                </div>

                <div>
                    <label class="field-label" for="remarks">Remarks (optional)</label>
                    <textarea id="remarks" v-model="form.data.remarks" rows="2" class="field-input" placeholder="Why is this transfer needed?"></textarea>
                </div>
            </div>

            <div class="panel p-5">
                <h2 class="mb-3 text-sm font-semibold text-ink">Items</h2>
                <VariantPicker :store-id="form.data.from_store_id" placeholder="Search for a product to add…" @select="addItem" />

                <table v-if="form.data.items.length" class="data-table mt-4">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Quantity requested</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in form.data.items" :key="item.product_variant_id">
                            <td>{{ item.variant_label }}</td>
                            <td class="figures text-ink-faint">{{ item.sku }}</td>
                            <td>
                                <input v-model="item.quantity_requested" type="number" min="1" class="field-input w-28" />
                            </td>
                            <td class="text-right">
                                <button type="button" class="text-xs text-brick-500" @click="removeItem(index)">Remove</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p v-else class="mt-3 text-sm text-ink-faint">No items added yet.</p>
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" class="btn-secondary" @click="router.push({ name: 'transfers.index' })">Cancel</button>
                <button type="submit" class="btn-primary" :disabled="form.processing">
                    {{ form.processing ? 'Submitting…' : 'Create transfer' }}
                </button>
            </div>
        </form>
    </div>
</template>
