<script setup>
import { computed } from 'vue';

const props = defineProps({
    labels: { type: Array, required: true },
    values: { type: Array, required: true },
    height: { type: Number, default: 160 },
});

const width = 560;
const padding = 24;

const points = computed(() => {
    if (!props.values.length) return [];
    const max = Math.max(...props.values, 1);
    const step = (width - padding * 2) / Math.max(props.values.length - 1, 1);

    return props.values.map((value, index) => {
        const x = padding + index * step;
        const y = props.height - padding - (value / max) * (props.height - padding * 2);
        return { x, y, value };
    });
});

const linePath = computed(() =>
    points.value.map((point, index) => `${index === 0 ? 'M' : 'L'}${point.x},${point.y}`).join(' ')
);

const areaPath = computed(() => {
    if (!points.value.length) return '';
    const first = points.value[0];
    const last = points.value[points.value.length - 1];
    return `${linePath.value} L${last.x},${props.height - padding} L${first.x},${props.height - padding} Z`;
});
</script>

<template>
    <svg :viewBox="`0 0 ${width} ${height}`" class="w-full" preserveAspectRatio="none">
        <line
            :x1="padding"
            :y1="height - padding"
            :x2="width - padding"
            :y2="height - padding"
            stroke="#DEE1E7"
        />
        <path :d="areaPath" fill="#1F3A5F" fill-opacity="0.08" />
        <path :d="linePath" fill="none" stroke="#1F3A5F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        <circle v-for="(point, i) in points" :key="i" :cx="point.x" :cy="point.y" r="2.5" fill="#1F3A5F" />
    </svg>
    <div class="mt-1 flex justify-between text-[11px] text-ink-faint">
        <span v-for="label in labels" :key="label">{{ label }}</span>
    </div>
</template>
