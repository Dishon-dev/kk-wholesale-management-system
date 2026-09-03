<script setup>
import { computed } from 'vue';
import { usePermissions } from '@/composables/usePermissions';

const props = defineProps({
    permission: { type: [String, Array], default: null },
});

const { can, canAny } = usePermissions();

const allowed = computed(() => {
    if (!props.permission) return true;
    return Array.isArray(props.permission) ? canAny(props.permission) : can(props.permission);
});
</script>

<template>
    <slot v-if="allowed" />
</template>
