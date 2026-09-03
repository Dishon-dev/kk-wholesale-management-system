<script setup>
import { computed } from 'vue';
import { useUiStore } from '@/stores/ui';
import { usePermissions } from '@/composables/usePermissions';
import { NAV_GROUPS } from '@/utils/navigation';
import NavIcon from './NavIcon.vue';

const ui = useUiStore();
const { can } = usePermissions();

const groups = computed(() =>
    NAV_GROUPS.map((group) => ({
        ...group,
        items: group.items.filter((item) => can(item.permission)),
    })).filter((group) => group.items.length > 0)
);
</script>

<template>
    <aside class="flex h-screen flex-col bg-brand-700 text-brand-50 transition-[width] duration-150"
        :class="ui.sidebarCollapsed ? 'w-18' : 'w-60'">
        <div class="flex h-14 items-center justify-between gap-2 border-b border-white/10 px-4">
            <div class="flex items-center gap-2">
                <span
                    class="shrink-0 items-center justify-center font-display text-sm font-bold text-white">
                    KK
                </span>
                <span v-if="!ui.sidebarCollapsed" class="font-display text-sm font-semibold tracking-tight">
                    Wholesalers
                </span>
            </div>

            <button type="button"
                class="flex h-6 w-6 shrink-0 items-center justify-center rounded text-brand-300 hover:text-white"
                @click="ui.toggleSidebar()">
                <svg width="14" height="14" viewBox="0 0 20 20" fill="none"
                    :class="ui.sidebarCollapsed ? 'rotate-180' : ''">
                    <path d="M12.5 5 7.5 10l5 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </button>
        </div>
        <nav class="flex-1 overflow-y-auto px-2 py-4">
            <div v-for="group in groups" :key="group.label" class="mb-5">
                <p v-if="!ui.sidebarCollapsed" class="mb-1.5 px-2.5 text-[11px] font-medium text-brand-300">
                    {{ group.label }}
                </p>
                <RouterLink v-for="item in group.items" :key="item.route" :to="{ name: item.route }"
                    class="mb-0.5 flex items-center gap-2.5 rounded px-2.5 py-2 text-sm text-brand-100 hover:bg-white/5 hover:text-white"
                    active-class="bg-white/10 text-white">
                    <NavIcon :name="item.icon" />
                    <span v-if="!ui.sidebarCollapsed">{{ item.label }}</span>
                </RouterLink>
            </div>
        </nav>
    </aside>
</template>
