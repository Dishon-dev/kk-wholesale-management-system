import { useUiStore } from '@/stores/ui';

/**
 * Usage: const confirmed = await confirm({ title, message });
 */
export function useConfirm() {
    const ui = useUiStore();
    return (options) => ui.confirm(options);
}
