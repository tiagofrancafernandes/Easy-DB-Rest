<script setup lang="ts">
import TopNavBar from './TopNavBar.vue';
import SideNavBar from './SideNavBar.vue';
</script>

<template>
    <div class="flex flex-col h-screen overflow-hidden bg-background">
        <TopNavBar />

        <div class="flex flex-1 pt-14 overflow-hidden">
            <SideNavBar />

            <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
                <slot />
            </main>
        </div>
    </div>
</template>
