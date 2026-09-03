<script setup>
import { ref } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import * as variantsService from '@/services/variants.service';

const props = defineProps({
    placeholder: { type: String, default: 'Search by product name, SKU or barcode…' },
    storeId: { type: [String, Number], default: null }, 
});

const emit = defineEmits(['select']);

const query = ref('');
const results = ref([]);
const open = ref(false);
const searching = ref(false);

const runSearch = useDebounceFn(async () => {
    if (query.value.trim().length < 2) {
        results.value = [];
        return;
    }
    searching.value = true;
    try {
        const { data } = await variantsService.search({ q: query.value, store_id: props.storeId });
        results.value = data;
        open.value = true;
    } finally {
        searching.value = false;
    }
}, 300);

function pick(variant) {
    emit('select', variant);
    query.value = '';
    results.value = [];
    open.value = false;
}
</script>

<template>
    <div class="relative">
        <input
            v-model="query"
            type="text"
            class="field-input"
            :placeholder="placeholder"
            @input="runSearch"
            @focus="results.length && (open = true)"
        />

        <div
            v-if="open && (results.length || searching)"
            class="absolute z-20 mt-1 w-full rounded border border-border bg-surface shadow-popover"
        >
            <p v-if="searching" class="px-3 py-2 text-xs text-ink-faint">Searching…</p>
            <button
                v-for="variant in results"
                :key="variant.id"
                type="button"
                class="flex w-full items-center justify-between px-3 py-2 text-left text-sm hover:bg-canvas"
                @click="pick(variant)"
            >
                <span>
                    <span class="text-ink">{{ variant.product?.name }}</span>
                    <span v-if="variant.name !== 'Default Variant'" class="text-ink-soft"> — {{ variant.name }}</span>
                </span>
                <span class="text-xs text-ink-faint">{{ variant.sku }}</span>
            </button>
            <p v-if="!searching && !results.length" class="px-3 py-2 text-xs text-ink-faint">No matches.</p>
        </div>
    </div>
</template>
