<script setup>
import { onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    title: { type: String, required: true },
    size: { type: String, default: 'md' },
});

const emit = defineEmits(['close']);

const widths = { sm: 'max-w-md', md: 'max-w-xl', lg: 'max-w-3xl' };

function onKeydown(event) {
    if (event.key === 'Escape') emit('close');
}

onMounted(() => document.addEventListener('keydown', onKeydown));
onBeforeUnmount(() => document.removeEventListener('keydown', onKeydown));
</script>

<template>
    <Teleport to="body">
        <div class="fixed inset-0 z-40 flex items-center justify-center overflow-y-auto bg-ink/40 px-4 py-10">
            <div class="absolute inset-0" @click="emit('close')" />
            <div
                class="relative z-10 w-full rounded-md border border-border bg-surface shadow-popover"
                :class="widths[size]"
                role="dialog"
                aria-modal="true"
            >
                <div class="flex items-center justify-between border-b border-border px-5 py-4">
                    <h2 class="font-display text-base font-semibold">{{ title }}</h2>
                    <button
                        type="button"
                        class="rounded p-1 text-ink-faint hover:bg-canvas hover:text-ink"
                        aria-label="Close"
                        @click="emit('close')"
                    >
                        <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                            <path d="M5 5l10 10M15 5L5 15" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                        </svg>
                    </button>
                </div>

                <div class="px-5 py-4">
                    <slot />
                </div>

                <div v-if="$slots.footer" class="flex items-center justify-end gap-2 border-t border-border px-5 py-3.5">
                    <slot name="footer" />
                </div>
            </div>
        </div>
    </Teleport>
</template>
