import { useUiStore } from '@/stores/ui';

export function useToast() {
    const ui = useUiStore();

    return {
        success: (message) => ui.notify(message, { tone: 'positive' }),
        error: (message) => ui.notify(message, { tone: 'negative' }),
        info: (message) => ui.notify(message, { tone: 'info' }),
    };
}
