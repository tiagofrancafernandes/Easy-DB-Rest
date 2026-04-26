<script setup lang="ts">
import { Icon } from '@iconify/vue';
import { useRouter, useRoute } from 'vue-router';

const router = useRouter();
const route = useRoute();

const navItems = [
    { name: 'Dashboard', icon: 'tabler:dashboard', path: '/' },
    { name: 'SQL Editor', icon: 'tabler:code', path: '/sql' },
    { name: 'Tables', icon: 'tabler:table-alias', path: '/tables' },
    { name: 'Functions', icon: 'tabler:math-function', path: '#' },
    { name: 'Storage', icon: 'tabler:database', path: '#' },
    { name: 'Authentication', icon: 'tabler:lock', path: '#' },
];

const isActive = (path: string) => route.path === path;
</script>

<template>
    <aside class="bg-zinc-950 h-full w-64 border-r border-zinc-800 flex flex-col py-4 space-y-2 shrink-0">
        <div class="px-4 mb-6">
            <div class="flex items-center gap-3 mb-1">
                <div class="w-8 h-8 bg-zinc-900 border border-emerald-400/30 rounded flex items-center justify-center">
                    <Icon icon="tabler:database-heart" class="text-emerald-400 text-lg" />
                </div>
                <div>
                    <h2 class="text-emerald-400 font-black text-sm tracking-tight">Project Alpha</h2>
                    <p class="text-[10px] text-zinc-500 uppercase font-bold tracking-widest">AWS-East-1</p>
                </div>
            </div>
            <button
                class="w-full mt-4 bg-primary-container text-on-primary-container text-xs font-bold py-2 rounded flex items-center justify-center gap-2 active:scale-[0.98] transition-transform"
            >
                <Icon icon="tabler:plus" class="text-sm" />
                NEW CONNECTION
            </button>
        </div>

        <nav class="flex-1 px-2 space-y-1 overflow-y-auto">
            <router-link
                v-for="item in navItems"
                :key="item.name"
                :to="item.path"
                class="flex items-center gap-3 px-3 py-2 transition-all duration-150 rounded group font-sans text-xs font-medium uppercase tracking-wider"
                :class="[
                    isActive(item.path)
                        ? 'text-emerald-400 border-l-2 border-emerald-400 bg-zinc-900/50'
                        : 'text-zinc-500 hover:text-zinc-200 hover:bg-zinc-900',
                ]"
            >
                <Icon :icon="item.icon" class="text-lg" />
                {{ item.name }}
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
