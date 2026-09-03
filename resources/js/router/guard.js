import { useAuthStore } from '@/stores/auth';

export function authGuard(to, from, next) {
    if (import.meta.env.VITE_SKIP_AUTH === 'true') {
        return next();
    }

    const auth = useAuthStore();

    if (to.meta.public) {
        if (to.name === 'login' && auth.isAuthenticated) {
            return next(auth.homeRoute);
        }

        return next();
    }

    if (!auth.isAuthenticated) {
        return next({
            name: 'login',
            query: { redirect: to.fullPath },
        });
    }

    if (to.meta.permission && !auth.can(to.meta.permission)) {
        return next({ name: 'forbidden' });
    }

    return next();
}
