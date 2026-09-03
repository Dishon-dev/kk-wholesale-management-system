import { computed } from 'vue';
import { useAuthStore } from '@/stores/auth';

export function usePermissions() {
    const auth = useAuthStore();

    return {
        can: (permission) => auth.can(permission),
        canAny: (permissions) => auth.canAny(permissions),
        roles: computed(() => auth.roles),
        isAdministrator: computed(() => auth.roles.includes('Super Admin')),
    };
}
