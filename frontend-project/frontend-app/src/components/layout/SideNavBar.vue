<script setup lang="ts">
import { Icon } from '@iconify/vue';
import { useRouter, useRoute } from 'vue-router';

const router = useRouter();
const route = useRoute();

const props = defineProps<{
    collapsed?: boolean;
}>();

const emit = defineEmits(['toggle']);

const navItems = [
    { name: 'Dashboard', icon: 'tabler:dashboard', path: '/' },
    { name: 'Connections', icon: 'tabler:plug-connected', path: '/connections' },
    { name: 'Teams', icon: 'tabler:users', path: '#' },
];

const isActive = (path: string) => route.path === path;
</script>

<template>
    <aside
        class="bg-zinc-950 h-full border-r border-zinc-800 flex flex-col py-4 space-y-2 shrink-0 transition-all duration-300"
        :class="[collapsed ? 'w-16' : 'w-64']"
    >
        <div class="px-4 mb-6 flex items-center justify-between overflow-hidden">
            <div v-if="!collapsed" class="flex items-center gap-3 animate-in fade-in duration-300">
                <div class="w-8 h-8 bg-zinc-900 border border-emerald-400/30 rounded flex items-center justify-center">
                    <Icon icon="tabler:database-heart" class="text-emerald-400 text-lg" />
                </div>
                <div>
                    <h2 class="text-emerald-400 font-black text-sm tracking-tight truncate w-32">Main Console</h2>
                </div>
            </div>
            <button
                @click="emit('toggle')"
                class="p-1.5 rounded bg-zinc-900 border border-zinc-800 text-zinc-500 hover:text-emerald-400 transition-colors mx-auto"
            >
                <Icon
                    :icon="collapsed ? 'tabler:layout-sidebar-right-expand' : 'tabler:layout-sidebar-left-collapse'"
                    class="text-lg"
                />
            </button>
        </div>

        <div v-if="!collapsed" class="px-4 mb-6">
            <button
                @click="router.push('/connections/new')"
                class="w-full bg-primary-container text-zinc-950 text-xs font-bold py-2 rounded flex items-center justify-center gap-2 active:scale-[0.98] transition-transform truncate"
            >
                <Icon icon="tabler:plus" class="text-sm" />
                NEW CONNECTION
            </button>
        </div>
        <div v-else class="flex justify-center mb-6">
            <button
                @click="router.push('/connections/new')"
                class="w-10 h-10 bg-primary-container text-zinc-950 rounded-full flex items-center justify-center active:scale-[0.98] transition-transform"
                title="New Connection"
            >
                <Icon icon="tabler:plus" class="text-lg" />
            </button>
        </div>

        <nav class="flex-1 px-2 space-y-1 overflow-y-auto overflow-x-hidden">
            <router-link
                v-for="item in navItems"
                :key="item.name"
                :to="item.path"
                class="flex items-center transition-all duration-150 rounded group font-sans text-xs font-medium uppercase tracking-wider relative"
                :class="[
                    isActive(item.path)
                        ? 'text-emerald-400 border-l-2 border-emerald-400 bg-zinc-900/50'
                        : 'text-zinc-500 hover:text-zinc-200 hover:bg-zinc-900',
                    collapsed ? 'justify-center py-3' : 'gap-3 px-3 py-2',
                ]"
                :title="collapsed ? item.name : ''"
            >
                <Icon :icon="item.icon" class="text-lg shrink-0" />
                <span v-if="!collapsed" class="truncate animate-in slide-in-from-left-2 duration-200">
                    {{ item.name }}
                </span>
            </router-link>
        </nav>

        <div class="px-2 pt-4 border-t border-zinc-900 space-y-1">
            <a
                class="flex items-center gap-3 px-3 py-2 text-zinc-500 hover:text-zinc-200 hover:bg-zinc-900 transition-all duration-150 rounded group font-sans text-xs font-medium uppercase tracking-wider"
                href="#"
            >
                <Icon icon="tabler:chart-bar" class="text-lg" />
                Usage
            </a>
            <a
                class="flex items-center gap-3 px-3 py-2 text-zinc-500 hover:text-zinc-200 hover:bg-zinc-900 transition-all duration-150 rounded group font-sans text-xs font-medium uppercase tracking-wider"
                href="#"
            >
                <Icon icon="tabler:settings" class="text-lg" />
                Settings
            </a>
        </div>
    </aside>
</template>
