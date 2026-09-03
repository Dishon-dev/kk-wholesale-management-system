<script setup>
import { useAuthStore } from '@/stores/auth';
import { useForm } from '@/composables/useForm';
import { useToast } from '@/composables/useToast';
import { required, minLength } from '@/utils/validators';
import * as authService from '@/services/auth.service';

const auth = useAuthStore();
const toast = useToast();

const form = useForm(
    { current_password: '', password: '', password_confirmation: '' },
    { rules: { current_password: [required], password: [required, minLength(8)] } }
);

async function handleSubmit() {
    if (form.data.password !== form.data.password_confirmation) {
        form.errors.password_confirmation = 'Passwords do not match.';
        return;
    }

    const { ok } = await form.submit((payload) => authService.changePassword(payload));
    if (ok) {
        toast.success('Password updated.');
        form.reset();
    }
}
</script>

<template>
    <div class="mx-auto max-w-lg">
        <h1 class="mb-5 text-xl font-semibold">My profile</h1>

        <div class="panel mb-5 p-5">
            <p class="text-sm font-medium text-ink">{{ auth.user?.name }}</p>
            <p class="text-sm text-ink-soft">{{ auth.user?.email }}</p>
            <p class="mt-2 text-xs text-ink-faint">{{ auth.roles.join(', ') }}</p>
        </div>

        <div class="panel p-5">
            <h2 class="mb-4 text-sm font-semibold text-ink">Change password</h2>
            <form class="space-y-4" @submit.prevent="handleSubmit">
                <div>
                    <label class="field-label" for="current-password">Current password</label>
                    <input id="current-password" v-model="form.data.current_password" type="password" class="field-input" autocomplete="current-password" />
                    <p v-if="form.errors.current_password" class="field-error">{{ form.errors.current_password }}</p>
                </div>
                <div>
                    <label class="field-label" for="new-password">New password</label>
                    <input id="new-password" v-model="form.data.password" type="password" class="field-input" autocomplete="new-password" />
                    <p v-if="form.errors.password" class="field-error">{{ form.errors.password }}</p>
                </div>
                <div>
                    <label class="field-label" for="confirm-password">Confirm new password</label>
                    <input id="confirm-password" v-model="form.data.password_confirmation" type="password" class="field-input" autocomplete="new-password" />
                    <p v-if="form.errors.password_confirmation" class="field-error">{{ form.errors.password_confirmation }}</p>
                </div>
                <button type="submit" class="btn-primary" :disabled="form.processing">
                    {{ form.processing ? 'Updating…' : 'Update password' }}
                </button>
            </form>
        </div>
    </div>
</template>
