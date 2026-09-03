<script setup>
defineProps({
    modelValue: { type: String, default: '' },
    placeholder: { type: String, default: 'Search…' },
});

defineEmits(['update:modelValue']);
</script>

<template>
    <div class="flex flex-wrap items-center gap-3 border-b border-border px-5 py-3.5">
        <div class="relative w-full max-w-xs">
            <svg
                class="pointer-events-none absolute left-2.5 top-2.5 text-ink-faint"
                width="15"
                height="15"
                viewBox="0 0 20 20"
                fill="none"
            >
                <circle cx="9" cy="9" r="6.5" stroke="currentColor" stroke-width="1.5" />
                <path d="M14 14l4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
            </svg>
            <input
                :value="modelValue"
                type="text"
                class="field-input pl-8"
                :placeholder="placeholder"
                @input="$emit('update:modelValue', $event.target.value)"
            />
        </div>

        <div class="flex flex-1 flex-wrap items-center gap-3">
            <slot name="filters" />
        </div>

        <div class="ml-auto flex items-center gap-2">
            <slot name="actions" />
        </div>
    </div>
</template>
