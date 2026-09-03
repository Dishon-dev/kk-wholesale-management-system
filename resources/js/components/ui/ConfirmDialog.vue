<script setup>
import { useUiStore } from '@/stores/ui';

const ui = useUiStore();
</script>

<template>
    <Teleport to="body">
        <div v-if="ui.confirmDialog" class="fixed inset-0 z-50 flex items-center justify-center bg-ink/40 px-4">
            <div class="w-full max-w-sm rounded-md border border-border bg-surface p-5 shadow-popover">
                <h2 class="font-display text-base font-semibold text-ink">{{ ui.confirmDialog.title }}</h2>
                <p class="mt-2 text-sm text-ink-soft">{{ ui.confirmDialog.message }}</p>

                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" class="btn-secondary" @click="ui.resolveConfirm(false)">
                        Cancel
                    </button>
                    <button
                        type="button"
                        :class="ui.confirmDialog.tone === 'danger' ? 'btn-danger' : 'btn-primary'"
                        @click="ui.resolveConfirm(true)"
                    >
                        {{ ui.confirmDialog.confirmLabel }}
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>
