<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { usePermissions } from '@/composables/usePermissions';
import { initials } from '@/utils/formatters';
import * as alertsService from '@/services/alerts.service';

const auth = useAuthStore();
const router = useRouter();
const { can } = usePermissions();

const menuOpen = ref(false);
const alertCount = ref(0);

onMounted(async () => {
    if (!can('alerts.view')) return;
    try {
        const { data } = await alertsService.unreadCount();
        alertCount.value = data.count;
    } catch {

    }
});

async function handleLogout() {
    await auth.logout();
    router.push({ name: 'login' });
}

</script>

<template>
    <header class="flex h-14 items-center justify-between border-b border-border bg-surface px-6">
        <div>
            <p class="text-xs text-ink-faint">Dashboard</p>
        </div>

        <div class="flex items-center gap-4">
            <RouterLink v-if="can('alerts.view')" :to="{ name: 'alerts.index' }"
                class="relative flex h-8 w-8 items-center justify-center rounded text-ink-soft hover:bg-canvas"
                aria-label="Stock alerts">
                <svg width="17" height="17" viewBox="0 0 20 20" fill="none">
                    <path d="M6 8a4 4 0 0 1 8 0c0 3 1 4 1 4H5s1-1 1-4Zm2 6a2 2 0 0 0 4 0" stroke="currentColor"
                        stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span v-if="alertCount > 0"
                    class="absolute -right-0.5 -top-0.5 flex h-4 min-w-[16px] items-center justify-center rounded-full bg-brick-500 px-1 text-[10px] font-medium text-white">
                    {{ alertCount > 99 ? '99+' : alertCount }}
                </span>
            </RouterLink>

            <div class="relative">
                <button type="button" class="flex items-center gap-2.5 rounded px-2 py-1.5 hover:bg-canvas"
                    @click="menuOpen = !menuOpen">
                    <span
                        class="flex h-7 w-7 items-center justify-center rounded-full bg-brand-100 text-xs font-semibold text-brand-700">
                        {{ initials(auth.user?.name) }}
                    </span>
                    <span class="text-sm text-ink">{{ auth.user?.name }}</span>
                    <svg class="h-3.5 w-3.5 text-ink-faint transition-transform duration-150"
                        :class="{ 'rotate-180': menuOpen }" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                    </svg>
                </button>

                <div v-if="menuOpen"
                    class="absolute right-0 top-11 w-56 rounded border border-border bg-surface shadow-popover"
                    @click="menuOpen = false">
                    <div class="border-b border-border px-3 py-2.5">
                        <p class="text-sm font-medium text-ink">{{ auth.user?.name }}</p>
                        <p class="mt-0.5 text-xs text-ink-faint">{{ auth.roles.join(', ') || 'No role assigned' }}</p>
                        <p v-if="auth.user?.branch?.name" class="mt-0.5 text-xs text-ink-faint">{{ auth.user.branch.name
                            }}</p>
                    </div>
                    <RouterLink :to="{ name: 'account.profile' }"
                        class="block px-3 py-2.5 text-sm text-ink hover:bg-canvas">
                        My profile
                    </RouterLink>
                    <div class="border-t border-border">
                        <button type="button"
                            class="block w-full px-3 py-2.5 text-left text-sm text-brick-500 hover:bg-canvas"
                            @click="handleLogout">
                            Sign out
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>
</template>
