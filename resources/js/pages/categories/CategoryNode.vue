<script setup>
import { ref } from 'vue';
import { usePermissions } from '@/composables/usePermissions';

const props = defineProps({
    category: { type: Object, required: true },
    depth: { type: Number, default: 0 },
});

const emit = defineEmits(['add-child', 'edit', 'delete']);

const { can } = usePermissions();
const expanded = ref(true);
</script>

<template>
    <li>
        <div class="group flex items-center justify-between rounded px-2 py-1.5 hover:bg-canvas" :style="{ paddingLeft: `${depth * 18 + 8}px` }">
            <div class="flex items-center gap-2">
                <button
                    v-if="category.children?.length"
                    type="button"
                    class="text-ink-faint"
                    @click="expanded = !expanded"
                    :aria-label="expanded ? 'Collapse' : 'Expand'"
                >
                    <svg width="12" height="12" viewBox="0 0 20 20" fill="none" :class="expanded ? 'rotate-90' : ''">
                        <path d="M7 5l6 5-6 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
                <span v-else class="inline-block w-3"></span>
                <span class="text-sm text-ink">{{ category.name }}</span>
                <span class="text-xs text-ink-faint">{{ category.products_count ?? 0 }} products</span>
            </div>

            <div class="hidden items-center gap-1 group-hover:flex">
                <button v-if="can('categories.create')" type="button" class="btn-ghost px-2 py-1 text-xs" @click="emit('add-child', category)">Add sub-category</button>
                <button v-if="can('categories.update')" type="button" class="btn-ghost px-2 py-1 text-xs" @click="emit('edit', category)">Edit</button>
                <button v-if="can('categories.delete')" type="button" class="btn-ghost px-2 py-1 text-xs text-brick-500" @click="emit('delete', category)">Delete</button>
            </div>
        </div>

        <ul v-if="expanded && category.children?.length">
            <CategoryNode
                v-for="child in category.children"
                :key="child.id"
                :category="child"
                :depth="depth + 1"
                @add-child="emit('add-child', $event)"
                @edit="emit('edit', $event)"
                @delete="emit('delete', $event)"
            />
        </ul>
    </li>
</template>
