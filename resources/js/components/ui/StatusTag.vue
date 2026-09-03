<script setup>
defineProps({
    label: { type: String, required: true },
    tone: { type: String, default: 'neutral' }, // neutral | positive | warning | negative | info
});
</script>

<template>
    <span :class="`tag-${tone}`">{{ label }}</span>
</template>
