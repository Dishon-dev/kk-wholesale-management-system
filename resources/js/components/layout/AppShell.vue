<script setup>
import Sidebar from './Sidebar.vue';
import Topbar from './Topbar.vue';
</script>

<template>
    <div class="flex h-screen bg-canvas">
        <Sidebar />
        <div class="flex flex-1 flex-col overflow-hidden">
            <Topbar />
            <main class="flex-1 overflow-y-auto px-6 py-6">
                <RouterView />
            </main>
        </div>
    </div>
</template>
