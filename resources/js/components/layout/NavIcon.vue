<script setup>
defineProps({
    name: { type: String, required: true },
});

const paths = {
    grid: 'M4 4h5v5H4V4Zm7 0h5v5h-5V4ZM4 11h5v5H4v-5Zm7 0h5v5h-5v-5Z',
    cart: 'M3 4h2l1.6 9.2a2 2 0 0 0 2 1.6h6a2 2 0 0 0 2-1.6L18 7H6M8 17.5a.5.5 0 1 0 0 1 .5.5 0 0 0 0-1Zm7 0a.5.5 0 1 0 0 1 .5.5 0 0 0 0-1Z',
    receipt: 'M6 3h8v14l-2-1.2L10 17l-2-1.2L6 17V3Zm2 4h4M8 10h4',
    boxes: 'M10 3 3 6.5 10 10l7-3.5L10 3ZM3 6.5V14L10 17.5M17 6.5V14L10 17.5M10 10v7.5',
    history: 'M4 10a6 6 0 1 1 2 4.5M4 10V6m0 4h4M10 6.5V10l2.5 1.5',
    sliders: 'M4 5h5M4 10h9M4 15h6M13 3.5v3M15 8.5v3M12 13v3',
    swap: 'M4 7h9l-2.5-2.5M16 13H7l2.5 2.5',
    bell: 'M6 8a4 4 0 0 1 8 0c0 3 1 4 1 4H5s1-1 1-4Zm2 6a2 2 0 0 0 4 0',
    tag: 'M4 4h6l7 7-6 6-7-7V4Zm3 3.01V7',
    folder: 'M3 5h5l1.5 2H17v8a1 1 0 0 1-1 1H3V5Z',
    building: 'M5 17V4h10v13M8 7h1M11 7h1M8 10h1M11 10h1M8 13h1M11 13h1M3 17h14',
    store: 'M4 8V4h12v4M4 8l1 9h10l1-9M4 8h12M8.5 17v-5h3v5',
    users: 'M7 9a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Zm7 0a2.2 2.2 0 1 0 0-4.4 2.2 2.2 0 0 0 0 4.4M3 16c0-2.5 2-4 4.5-4S11 13.5 11 16M11.5 12.4c2 .3 3.5 1.7 3.5 3.6',
    shield: 'M10 3l6 2v5c0 4-2.5 6.5-6 7-3.5-.5-6-3-6-7V5l6-2Z',
    clock: 'M10 5.5V10l3 2M10 17a7 7 0 1 0 0-14 7 7 0 0 0 0 14Z',
};
</script>

<template>
    <svg width="16" height="16" viewBox="0 0 20 20" fill="none" aria-hidden="true">
        <path :d="paths[name]" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
</template>
