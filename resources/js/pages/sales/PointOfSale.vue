<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useToast } from '@/composables/useToast';
import * as storesService from '@/services/stores.service';
import * as salesService from '@/services/sales.service';
import { formatCurrency } from '@/utils/formatters';
import VariantPicker from '@/components/domain/VariantPicker.vue';

const router = useRouter();
const toast = useToast();
const auth = useAuthStore();

const stores = ref([]);
const storeId = ref('');
const cart = ref([]);
const discount = ref(0);
const taxRate = ref(0); // percentage, e.g. 16 for 16% VAT
const processing = ref(false);

onMounted(async () => {
    const { data } = await storesService.list({ per_page: 100, status: 1 });
    stores.value = data;
    storeId.value = auth.user?.store_id ?? data[0]?.id ?? '';
});

function addToCart(variant) {
    const existing = cart.value.find((line) => line.product_variant_id === variant.id);
    if (existing) {
        existing.quantity += 1;
        return;
    }
    cart.value.push({
        product_variant_id: variant.id,
        label: `${variant.product?.name} — ${variant.name}`,
        sku: variant.sku,
        unit_price: Number(variant.selling_price),
        quantity: 1,
        discount: 0,
    });
}

function removeLine(index) {
    cart.value.splice(index, 1);
}

const subtotal = computed(() =>
    cart.value.reduce((sum, line) => sum + line.unit_price * line.quantity - Number(line.discount || 0), 0)
);
const tax = computed(() => (subtotal.value - discount.value) * (Number(taxRate.value) / 100));
const grandTotal = computed(() => subtotal.value - discount.value + tax.value);

async function handleCheckout() {
    if (!cart.value.length) {
        toast.error('Add at least one item before checking out.');
        return;
    }

    processing.value = true;
    try {
        const { data: sale } = await salesService.create({
            store_id: storeId.value,
            discount: discount.value,
            tax: tax.value,
            items: cart.value.map(({ product_variant_id, quantity, unit_price, discount }) => ({
                product_variant_id,
                quantity,
                unit_price,
                discount,
            })),
        });

        toast.success(`Sale ${sale.invoice_number} recorded.`);
        cart.value = [];
        discount.value = 0;
        router.push({ name: 'sales.show', params: { id: sale.id } });
    } catch (error) {
        toast.error(error.message);
    } finally {
        processing.value = false;
    }
}
</script>

<template>
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-5">
        <div class="lg:col-span-3">
            <div class="mb-4 flex items-center justify-between">
                <h1 class="text-xl font-semibold">New sale</h1>
                <select v-if="stores.length > 1" v-model="storeId" class="field-input w-auto">
                    <option v-for="store in stores" :key="store.id" :value="store.id">{{ store.name }}</option>
                </select>
            </div>

            <div class="panel p-5">
                <VariantPicker :store-id="storeId" placeholder="Scan or search a product to add…" @select="addToCart" />
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="panel flex h-full flex-col">
                <div class="panel-header">
                    <h2 class="text-sm font-semibold text-ink">Cart</h2>
                    <span class="text-xs text-ink-faint">{{ cart.length }} item{{ cart.length === 1 ? '' : 's' }}</span>
                </div>

                <div class="flex-1 divide-y divide-border overflow-y-auto">
                    <p v-if="!cart.length" class="px-5 py-10 text-center text-sm text-ink-faint">Cart is empty.</p>
                    <div v-for="(line, index) in cart" :key="line.product_variant_id" class="px-5 py-3">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-ink">{{ line.label }}</p>
                                <p class="text-xs text-ink-faint">{{ line.sku }} · {{ formatCurrency(line.unit_price) }} each</p>
                            </div>
                            <button type="button" class="text-xs text-brick-500" @click="removeLine(index)">Remove</button>
                        </div>
                        <div class="mt-2 flex items-center gap-3">
                            <label class="text-xs text-ink-soft">Qty</label>
                            <input v-model.number="line.quantity" type="number" min="1" class="field-input w-16 py-1" />
                            <label class="text-xs text-ink-soft">Discount</label>
                            <input v-model.number="line.discount" type="number" min="0" class="field-input w-20 py-1" />
                            <span class="ml-auto figures text-sm font-medium">
                                {{ formatCurrency(line.unit_price * line.quantity - (line.discount || 0)) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="space-y-2 border-t border-border px-5 py-4 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-ink-soft">Subtotal</span>
                        <span class="figures">{{ formatCurrency(subtotal) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-ink-soft">Order discount</span>
                        <input v-model.number="discount" type="number" min="0" class="field-input w-24 py-1 text-right" />
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-ink-soft">Tax rate (%)</span>
                        <input v-model.number="taxRate" type="number" min="0" class="field-input w-24 py-1 text-right" />
                    </div>
                    <div class="flex items-center justify-between border-t border-border pt-2 text-base font-semibold text-ink">
                        <span>Total</span>
                        <span class="figures">{{ formatCurrency(grandTotal) }}</span>
                    </div>
                </div>

                <div class="px-5 pb-5">
                    <button type="button" class="btn-primary w-full" :disabled="processing || !cart.length" @click="handleCheckout">
                        {{ processing ? 'Recording sale…' : 'Complete sale' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
