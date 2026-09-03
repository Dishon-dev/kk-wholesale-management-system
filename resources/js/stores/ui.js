import { defineStore } from 'pinia';

let nextToastId = 0;

export const useUiStore = defineStore('ui', {
    state: () => ({
        toasts: [],
        sidebarCollapsed: false,
        confirmDialog: null,
    }),

    actions: {
        notify(message, { tone = 'info', duration = 4500 } = {}) {
            const id = ++nextToastId;
            this.toasts.push({ id, message, tone });

            if (duration) {
                setTimeout(() => this.dismissToast(id), duration);
            }
        },

        dismissToast(id) {
            this.toasts = this.toasts.filter((toast) => toast.id !== id);
        },

        toggleSidebar() {
            this.sidebarCollapsed = !this.sidebarCollapsed;
        },

        /**
         * Opens the shared confirmation modal and resolves to `true`/`false`
         * depending on which button the person clicks. Anywhere in the app
         * that needs a "are you sure?" step awaits this instead of shipping
         * its own modal.
         */
        confirm({ title, message, confirmLabel = 'Confirm', tone = 'danger' }) {
            return new Promise((resolve) => {
                this.confirmDialog = { title, message, confirmLabel, tone, resolve };
            });
        },

        resolveConfirm(result) {
            this.confirmDialog?.resolve(result);
            this.confirmDialog = null;
        },
    },
});
