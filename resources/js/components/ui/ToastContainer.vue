<script setup>
import { useUiStore } from '@/stores/ui';

const ui = useUiStore();

const toneClasses = {
    positive: 'border-l-moss-500',
    negative: 'border-l-brick-500',
    info: 'border-l-brand-500',
};
</script>

<template>
    <Teleport to="body">
        <div class="fixed bottom-5 right-5 z-50 flex w-80 flex-col gap-2">
            <TransitionGroup name="toast">
                <div
                    v-for="toast in ui.toasts"
                    :key="toast.id"
                    class="flex items-start justify-between gap-3 rounded border-l-2 bg-surface px-4 py-3 text-sm shadow-popover"
                    :class="toneClasses[toast.tone] ?? toneClasses.info"
                >
                    <p class="text-ink">{{ toast.message }}</p>
                    <button
                        type="button"
                        class="shrink-0 text-ink-faint hover:text-ink"
                        aria-label="Dismiss"
                        @click="ui.dismissToast(toast.id)"
                    >
                        <svg width="14" height="14" viewBox="0 0 20 20" fill="none">
                            <path d="M5 5l10 10M15 5L5 15" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                        </svg>
                    </button>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.18s ease;
}
.toast-enter-from {
    opacity: 0;
    transform: translateY(6px);
}
.toast-leave-to {
    opacity: 0;
    transform: translateX(12px);
}
</style>
