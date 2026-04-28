<script setup lang="ts">
import { onMounted } from 'vue';
import { Icon } from '@iconify/vue';
import { useConnections } from '@/composables/useConnections';
import { useRouter } from 'vue-router';

const { connections, fetchConnections } = useConnections();
const router = useRouter();

onMounted(fetchConnections);
</script>

<template>
    <div class="flex-1 bg-surface-container-lowest overflow-y-auto p-8">
        <div class="max-w-7xl mx-auto space-y-12">
            <!-- Hero -->
            <section class="flex flex-col md:flex-row gap-12 items-center">
                <div class="flex-1 space-y-6">
                    <h1 class="text-5xl font-black text-on-surface tracking-tighter leading-none">
                        Universal
                        <span class="text-emerald-400">Database</span>
                        Control Plane
                    </h1>
                    <p class="text-on-surface-variant text-lg leading-relaxed max-w-xl">
                        A high-performance SQL console and schema manager for PostgreSQL, MySQL, SQLite, and SQL Server.
                    </p>
                    <div class="flex gap-4">
                        <button
                            @click="router.push('/connections')"
                            class="bg-emerald-500 text-zinc-950 px-8 py-3 rounded-sm font-black text-xs tracking-widest hover:bg-emerald-400 transition-all flex items-center gap-2"
                        >
                            BROWSE CONNECTIONS
                            <Icon icon="tabler:arrow-right" class="text-lg" />
                        </button>
                    </div>
                </div>
                <div class="flex-1 w-full grid grid-cols-2 gap-4">
                    <div class="bg-surface border border-emerald-400/20 p-6 rounded-sm shadow-emerald-500/5 shadow-2xl">
                        <Icon icon="tabler:plug-connected" class="text-3xl text-emerald-400 mb-4" />
                        <div class="text-2xl font-black text-on-surface">{{ connections.length }}</div>
                        <div class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest mt-1">
                            Active Connections
                        </div>
                    </div>
                    <div class="bg-surface border border-border p-6 rounded-sm">
                        <Icon icon="tabler:history" class="text-3xl text-zinc-700 mb-4" />
                        <div class="text-2xl font-black text-zinc-500">0</div>
                        <div class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest mt-1">
                            Recent Queries
                        </div>
                    </div>
                </div>
            </section>

            <!-- Quick Start -->
            <section>
                <h2 class="text-[10px] font-black uppercase tracking-[0.2em] text-zinc-600 mb-6">Quick Start</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div
                        v-for="i in [
                            {
                                t: 'Execute SQL',
                                d: 'Run raw queries with autocomplete and multi-result support.',
                                i: 'tabler:terminal-2',
                                p: '/connections',
                            },
                            {
                                t: 'Browse Schema',
                                d: 'Visualize tables, columns, constraints and relationships.',
                                i: 'tabler:table',
                                p: '/connections',
                            },
                            {
                                t: 'Team Access',
                                d: 'Share connections with your team securely via access tokens.',
                                i: 'tabler:users',
                                p: '#',
                            },
                        ]"
                        :key="i.t"
                        class="bg-surface border border-border p-8 rounded-sm hover:border-emerald-400/30 transition-all cursor-pointer group"
                        @click="router.push(i.p)"
                    >
                        <Icon
                            :icon="i.i"
                            class="text-3xl text-zinc-700 group-hover:text-emerald-400 transition-colors mb-6"
                        />
                        <h3 class="font-bold text-on-surface mb-2">{{ i.t }}</h3>
                        <p class="text-sm text-zinc-500 leading-relaxed">{{ i.d }}</p>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>
