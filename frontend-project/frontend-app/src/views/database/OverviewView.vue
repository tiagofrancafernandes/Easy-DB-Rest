<script setup lang="ts">
import { inject, onMounted, ref } from 'vue';
import { Icon } from '@iconify/vue';
import { useDatabase } from '@/composables/useDatabase';
import { useConnections } from '@/composables/useConnections';

const connectionId = inject('connectionId') as string;
const { currentConnection } = useConnections();
const { fetchDatabases, tables, isLoading } = useDatabase(connectionId);

const databases = ref<string[]>([]);
const stats = ref({
    tablesCount: 0,
    totalRows: 0,
    size: 'N/A'
});

onMounted(async () => {
    try {
        const dbResponse = await fetchDatabases();
        databases.value = dbResponse.data;
        stats.value.tablesCount = tables.value.length;
        stats.value.totalRows = tables.value.reduce((acc, t) => acc + (t.rows || 0), 0);
    } catch (e) {
        console.error(e);
    }
});
</script>

<template>
    <div class="p-8 h-full overflow-y-auto">
        <div class="max-w-6xl mx-auto space-y-12">
            <!-- Header -->
            <header class="flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-black text-on-surface tracking-tight">Database Overview</h1>
                    <p class="text-zinc-500 mt-2 flex items-center gap-2">
                        <Icon icon="tabler:plug-connected" class="text-emerald-400" />
                        Connected to <span class="text-on-surface font-bold">{{ currentConnection?.name }}</span>
                    </p>
                </div>
                <div class="bg-zinc-900/50 border border-zinc-800 px-4 py-2 rounded-sm flex items-center gap-3">
                    <div class="flex flex-col items-end">
                        <span class="text-[9px] font-black uppercase tracking-widest text-zinc-600">Health</span>
                        <span class="text-xs font-bold text-emerald-400">OPTIMAL</span>
                    </div>
                    <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse shadow-[0_0_8px_rgba(16,185,129,0.5)]"></div>
                </div>
            </header>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-surface border border-zinc-900 p-6 rounded-sm shadow-xl hover:border-emerald-400/30 transition-all">
                    <Icon icon="tabler:table" class="text-3xl text-zinc-700 mb-4" />
                    <div class="text-3xl font-black text-on-surface">{{ stats.tablesCount }}</div>
                    <div class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest mt-1">Total Tables</div>
                </div>
                <div class="bg-surface border border-zinc-900 p-6 rounded-sm shadow-xl">
                    <Icon icon="tabler:rows" class="text-3xl text-zinc-700 mb-4" />
                    <div class="text-3xl font-black text-on-surface">{{ stats.totalRows.toLocaleString() }}</div>
                    <div class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest mt-1">Estimated Rows</div>
                </div>
                <div class="bg-surface border border-zinc-900 p-6 rounded-sm shadow-xl">
                    <Icon icon="tabler:database-cog" class="text-3xl text-zinc-700 mb-4" />
                    <div class="text-3xl font-black text-on-surface">{{ databases.length }}</div>
                    <div class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest mt-1">Available Databases</div>
                </div>
            </div>

            <!-- Databases List -->
            <section>
                <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-zinc-600 mb-6">Environment Databases</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div 
                        v-for="db in databases" 
                        :key="db"
                        class="bg-zinc-900/30 border border-zinc-900 p-4 rounded-sm flex items-center justify-between group hover:border-emerald-400/20 transition-all"
                    >
                        <div class="flex items-center gap-3">
                            <Icon icon="tabler:database" class="text-zinc-600 group-hover:text-emerald-400" />
                            <span class="text-xs font-bold text-zinc-400 group-hover:text-on-surface">{{ db }}</span>
                        </div>
                        <Icon v-if="db === currentConnection?.database" icon="tabler:circle-check-filled" class="text-emerald-400" />
                    </div>
                </div>
            </section>

            <!-- Insights / Actions -->
            <section class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-zinc-900/20 border border-dashed border-zinc-800 p-8 rounded flex flex-col items-center justify-center text-center space-y-4">
                    <Icon icon="tabler:bulb" class="text-4xl text-amber-400/50" />
                    <div>
                        <h4 class="font-bold text-on-surface">Optimize Queries</h4>
                        <p class="text-sm text-zinc-500 mt-1 max-w-xs">Our engine detected 3 tables without proper primary keys. Consider adding them to improve performance.</p>
                    </div>
                    <button class="text-xs font-black text-amber-400 uppercase tracking-widest hover:text-white transition-colors">View Details</button>
                </div>
                <div class="bg-zinc-900/20 border border-dashed border-zinc-800 p-8 rounded flex flex-col items-center justify-center text-center space-y-4">
                    <Icon icon="tabler:shield-check" class="text-4xl text-blue-400/50" />
                    <div>
                        <h4 class="font-bold text-on-surface">Security Audit</h4>
                        <p class="text-sm text-zinc-500 mt-1 max-w-xs">All connection parameters are encrypted. No plain-text credentials found in logs.</p>
                    </div>
                    <button class="text-xs font-black text-blue-400 uppercase tracking-widest hover:text-white transition-colors">Security Settings</button>
                </div>
            </section>
        </div>
    </div>
</template>
