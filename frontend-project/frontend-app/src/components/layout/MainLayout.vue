<script setup lang="ts">
import { ref } from 'vue';
import TopNavBar from './TopNavBar.vue';
import SideNavBar from './SideNavBar.vue';

const isCollapsed = ref(localStorage.getItem('sidebar_collapsed') === 'true');

const toggleSidebar = () => {
    isCollapsed.value = !isCollapsed.value;
    localStorage.setItem('sidebar_collapsed', String(isCollapsed.value));
};
</script>

<template>
    <div class="flex flex-col h-screen overflow-hidden bg-background">
        <TopNavBar />

        <div class="flex flex-1 pt-14 overflow-hidden">
            <SideNavBar :collapsed="isCollapsed" @toggle="toggleSidebar" />

            <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
                <slot />
            </main>
        </div>
    </div>
</template>
