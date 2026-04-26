<script setup lang="ts">
import { Icon } from '@iconify/vue';

const appName = import.meta.env.VITE_APP_NAME || 'Console';
</script>

<template>
    <header
        class="bg-zinc-950 flex justify-between items-center h-14 px-4 w-full z-50 border-b border-zinc-800 fixed top-0"
    >
        <div class="flex items-center gap-6">
            <span class="text-lg font-bold text-white tracking-tight">{{ appName }}</span>
            <div class="hidden md:flex gap-4">
                <a
                    class="text-zinc-400 hover:text-emerald-400 hover:bg-zinc-900 transition-colors font-sans text-sm tracking-tight px-2 py-1 rounded"
                    href="#"
                >
                    Docs
                </a>
                <a
                    class="text-zinc-400 hover:text-emerald-400 hover:bg-zinc-900 transition-colors font-sans text-sm tracking-tight px-2 py-1 rounded"
                    href="#"
                >
                    Feedback
                </a>
                <a
                    class="text-zinc-400 hover:text-emerald-400 hover:bg-zinc-900 transition-colors font-sans text-sm tracking-tight px-2 py-1 rounded"
                    href="#"
                >
                    Support
                </a>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <div class="relative flex items-center bg-zinc-900 border border-zinc-800 rounded px-3 py-1.5 w-64">
                <Icon icon="tabler:search" class="text-zinc-500 text-sm mr-2" />
                <input
                    class="bg-transparent border-none focus:ring-0 p-0 text-xs text-zinc-300 w-full"
                    placeholder="Search resources..."
                    type="text"
                />
                <span class="text-[10px] text-zinc-600 bg-zinc-950 px-1 border border-zinc-800 rounded">⌘K</span>
            </div>
            <button class="text-zinc-400 hover:text-emerald-400 p-1.5 rounded transition-all">
                <Icon icon="tabler:bell" class="text-xl" />
            </button>
            <button class="text-zinc-400 hover:text-emerald-400 p-1.5 rounded transition-all">
                <Icon icon="tabler:settings" class="text-xl" />
            </button>
            <div
                class="w-8 h-8 rounded-full bg-emerald-400 flex items-center justify-center text-zinc-950 font-bold text-xs overflow-hidden"
            >
                <img
                    class="w-full h-full object-cover"
                    src="https://ui-avatars.com/api/?name=User&background=6dffba&color=003822"
                    alt="Profile"
                />
            </div>
        </div>
    </header>
</template>
