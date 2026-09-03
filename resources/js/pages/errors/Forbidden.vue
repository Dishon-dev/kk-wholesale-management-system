<script setup>
import { useRouter } from 'vue-router';
const router = useRouter();
</script>

<template>
    <div class="flex h-full flex-col items-center justify-center py-24 text-center">
        <p class="font-display text-5xl font-semibold text-brand-300">403</p>
        <h1 class="mt-3 text-lg font-semibold text-ink">You don't have access to this page</h1>
        <p class="mt-1 max-w-sm text-sm text-ink-soft">
            If you think this is a mistake, ask an administrator to check your role and permissions.
        </p>
        <button type="button" class="btn-primary mt-6" @click="router.back()">Go back</button>
    </div>
</template>
