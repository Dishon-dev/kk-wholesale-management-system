<script setup>
import { ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useForm } from '@/composables/useForm';
import { required, isEmail } from '@/utils/validators';
const auth = useAuthStore();
const router = useRouter();
const route = useRoute();
const showPassword = ref(false);
const form = useForm(
    {
        email: '',
        password: '',
        remember: false,
    },
    {
        rules: {
            email: [required, isEmail],
            password: [required],
        },
    }
);
async function handleSubmit() {
    const { ok, error } = await form.submit(payload => auth.login(payload));
    if (ok) {
        router.push(route.query.redirect || auth.homeRoute);
        return;
    }
    if (error && !Object.keys(form.errors).length) {
        form.setServerErrors({
            email: [error.message],
        });
    }
}
</script>
<template>
    <div class="min-h-screen bg-brand-700 lg:grid lg:grid-cols-2">
        <section class="hidden lg:flex text-white p-16 flex-col justify-between">

            <div>

                <h1
                    class="text-white font-bold text-xl">
                    KK
                </h1>

                <p class="mt-1 text-sm font-semibold text-brand-200 capitalize">
                    Wholesalers
                </p>

            </div>

            <div class="max-w-lg">

                <h1 class="text-5xl font-bold leading-tight">
                    <span class="text-brand-200 capitalize">store and stock movement.</span>
                </h1>

                <p class="mt-8 text-lg text-brand-100 leading-9">
                    Track inventory, sales and transfers across all branches in real
                    time with complete audit history and operational reporting.
                </p>

            </div>

            <p class="text-brand-300 text-sm">
                Access is granted by administrator.
            </p>

        </section>

        <section class="flex items-center justify-center px-6 py-12">

            <form @submit.prevent="handleSubmit" class="w-full max-w-xl rounded-3xl bg-white shadow-2xl p-8 md:p-10">

                <div>

                    <h2 class="text-3xl font-bold text-gray-900">
                        Welcome Back
                    </h2>

                    <p class="mt-2 text-gray-500">
                        Sign in to continue to Wholesale Operations.
                    </p>

                </div>

                <div class="mt-8">

                    <label class="block text-sm font-medium mb-2">
                        Email Address
                    </label>

                    <input v-model="form.data.email" type="email" autocomplete="username"
                        class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 transition-colors focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/20">

                    <p v-if="form.errors.email" class="mt-2 text-sm text-red-600">
                        {{ form.errors.email }}
                    </p>

                </div>

                <div class="mt-6">

                    <div class="flex justify-between items-center mb-2">

                        <label class="text-sm font-medium">
                            Password
                        </label>

                        <RouterLink to="/forgot-password" class="text-sm text-brand-600 hover:underline">

                            Forgot Password?

                        </RouterLink>

                    </div>

                    <div class="relative">

                        <input v-model="form.data.password" :type="showPassword ? 'text' : 'password'"
                            autocomplete="current-password"
                            class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 pr-12 text-gray-900 transition-colors focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/20">

                        <button type="button"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                            @click="showPassword = !showPassword">

                            <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm6-1s-3-7-9-7-9 7-9 7 3 7 9 7 9-7 9-7z" />
                            </svg>

                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-6 0-9-7-9-7a17.43 17.43 0 014.35-4.95M9.88 9.88a3 3 0 104.24 4.24M6.1 6.1L3 3m18 18-3.1-3.1" />
                            </svg>

                        </button>

                    </div>

                    <p v-if="form.errors.password" class="mt-2 text-sm text-red-600">
                        {{ form.errors.password }}
                    </p>

                </div>

                <div class="mt-6 flex items-center">

                    <input v-model="form.data.remember" type="checkbox"
                        class="h-4 w-4 rounded border-gray-300 accent-brand-600">

                    <span class="ml-3 text-sm text-gray-600">
                        Remember me
                    </span>

                </div>

                <button
                    class="mt-8 w-full rounded-full bg-brand-600 py-3.5 font-semibold text-white shadow-lg shadow-brand-600/30 transition-colors hover:bg-brand-700 disabled:opacity-60 disabled:shadow-none"
                    :disabled="form.processing">

                    {{ form.processing ? 'Signing in...' : 'Sign In' }}

                </button>

                <div class="mt-8 border-t pt-6">

                    <p class="text-center text-sm text-gray-500">
                        &copy;2026 KK Wholesalers . All rights reserved.
                    </p>

                </div>

            </form>

        </section>
    </div>
</template>