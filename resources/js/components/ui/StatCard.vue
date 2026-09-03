<script setup>
defineProps({
    label: { type: String, required: true },
    value: { type: String, required: true },
    hint: { type: String, default: '' },
    tone: { type: String, default: 'neutral' },
});

const accentByTone = {
    neutral: 'border-l-brand-500',
    warning: 'border-l-amber-500',
    negative: 'border-l-brick-500',
};
</script>

<template>
    <div class="panel border-l-2 px-5 py-4" :class="accentByTone[tone]">
        <p class="text-xs font-medium text-ink-soft">{{ label }}</p>
        <p class="mt-2 figures text-2xl font-semibold text-ink">{{ value }}</p>
        <p v-if="hint" class="mt-1 text-xs text-ink-faint">{{ hint }}</p>
    </div>
</template>
