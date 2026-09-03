import { defineStore } from 'pinia';
import * as authService from '@/services/auth.service';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        roles: [],
        permissions: [],
        status: 'idle', // idle | loading | ready
    }),

    getters: {
        isAuthenticated: (state) => Boolean(state.user),

        can: (state) => (permission) => {
            if (state.roles.includes('Administrator')) return true;

            if (import.meta.env.VITE_SKIP_AUTH === 'true') return true;
            return state.permissions.includes(permission);
        },

        canAny: (state) => (permissionList) =>
            permissionList.some((permission) => state.can(permission)),

        homeRoute: (state) => {
            if (state.can('dashboard.view')) return { name: 'dashboard' };
            if (state.can('sales.create')) return { name: 'sales.pos' };
            return { name: 'account.profile' };
        },
    },

    actions: {
        async fetchCurrentUser() {
            this.status = 'loading';
        
            try {
                const response = await authService.me();
        
                this.setSession(response.data);
            } catch (error) {
                this.clearSession();
            } finally {
                this.status = 'ready';
            }
        },

        async login(credentials) {
            this.status = 'loading';
        
            try {
                await authService.login(credentials);
        
                const response = await authService.me();
        
                this.setSession(response.data);
        
                this.status = 'ready';
            } catch (error) {
                this.clearSession();
                this.status = 'idle';
        
                throw error;
            }
        },

        async logout() {
            try {
                await authService.logout();
            } finally {
                this.clearSession();
            }
        },

        setSession(payload) {
            this.user = payload.user;
            this.roles = payload.roles ?? [];
            this.permissions = payload.permissions ?? [];
        },

        clearSession() {
            this.user = null;
            this.roles = [];
            this.permissions = [];
        },
    },
});
