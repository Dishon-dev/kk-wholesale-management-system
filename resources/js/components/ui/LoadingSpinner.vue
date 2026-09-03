<script setup>
defineProps({
    size: { type: Number, default: 16 },
    label: { type: String, default: 'Loading' },
});
</script>

<template>
    <span class="inline-flex items-center gap-2 text-ink-soft" role="status">
        <svg :width="size" :height="size" viewBox="0 0 24 24" class="animate-spin">
            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" fill="none" opacity="0.2" />
            <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" fill="none" stroke-linecap="round" />
        </svg>
        <span class="text-xs">{{ label }}</span>
    </span>
</template>
