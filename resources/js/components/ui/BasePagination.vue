<script setup>
import { computed } from 'vue';

const props = defineProps({
    currentPage: { type: Number, required: true },
    lastPage: { type: Number, required: true },
    total: { type: Number, required: true },
});

const emit = defineEmits(['change']);

const pages = computed(() => {
    const span = 2;
    const start = Math.max(1, props.currentPage - span);
    const end = Math.min(props.lastPage, props.currentPage + span);
    const list = [];
    for (let page = start; page <= end; page++) list.push(page);
    return list;
});
</script>

<template>
    <div v-if="lastPage > 1" class="flex items-center justify-between border-t border-border px-5 py-3">
        <p class="text-xs text-ink-soft">{{ total }} total record{{ total === 1 ? '' : 's' }}</p>

        <div class="flex items-center gap-1">
            <button
                type="button"
                class="btn-ghost px-2 py-1 text-xs"
                :disabled="currentPage === 1"
                @click="emit('change', currentPage - 1)"
            >
                Previous
            </button>

            <button
                v-for="page in pages"
                :key="page"
                type="button"
                class="h-7 w-7 rounded text-xs font-medium"
                :class="page === currentPage ? 'bg-brand-500 text-white' : 'text-ink-soft hover:bg-canvas'"
                @click="emit('change', page)"
            >
                {{ page }}
            </button>

            <button
                type="button"
                class="btn-ghost px-2 py-1 text-xs"
                :disabled="currentPage === lastPage"
                @click="emit('change', currentPage + 1)"
            >
                Next
            </button>
        </div>
    </div>
</template>
