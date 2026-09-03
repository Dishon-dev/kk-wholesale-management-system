<script setup>
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const auth = useAuthStore();
</script>

<template>
    <div class="flex h-screen flex-col items-center justify-center bg-canvas text-center">
        <p class="font-display text-5xl font-semibold text-brand-300">404</p>
        <h1 class="mt-3 text-lg font-semibold text-ink">Page not found</h1>
        <p class="mt-1 max-w-sm text-sm text-ink-soft">That page doesn't exist, or may have moved.</p>
        <button type="button" class="btn-primary mt-6" @click="router.push(auth.isAuthenticated ? auth.homeRoute : { name: 'login' })">
            Take me home
        </button>
    </div>
</template>
