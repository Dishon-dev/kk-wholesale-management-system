<script setup>
defineProps({
    title: { type: String, required: true },
    message: { type: String, default: '' },
});
</script>

<template>
    <div class="flex flex-col items-center justify-center px-6 py-16 text-center">
        <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-full border border-border text-ink-faint">
            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                <path d="M4 6h12M4 10h12M4 14h8" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" />
            </svg>
        </div>
        <p class="font-display text-sm font-semibold text-ink">{{ title }}</p>
        <p v-if="message" class="mt-1 max-w-xs text-xs text-ink-soft">{{ message }}</p>
        <div v-if="$slots.action" class="mt-4"><slot name="action" /></div>
    </div>
</template>
