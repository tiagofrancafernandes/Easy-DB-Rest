<script setup lang="ts">
import { ref, inject } from 'vue';
import { Icon } from '@iconify/vue';
import { useDatabase } from '@/composables/useDatabase';
import type { QueryResult } from '@/types';

const connectionId = inject('connectionId') as string;
const { executeQuery, isLoading } = useDatabase(connectionId);

const sql = ref('SELECT * FROM users LIMIT 10;');
const result = ref<QueryResult | null>(null);
const error = ref<string | null>(null);

const runQuery = async () => {
    error.value = null;
    try {
        result.value = await executeQuery(sql.value);
    } catch (e: any) {
        error.value = e.message || 'An error occurred while executing the query.';
        result.value = null;
    }
};

const clearQuery = () => {
    sql.value = '';
    result.value = null;
    error.value = null;
};
</script>

<template>
    <div class="h-full flex flex-col bg-surface overflow-hidden">
        <!-- Toolbar -->
        <div class="h-12 border-b border-zinc-900 bg-surface-low flex items-center justify-between px-4 shrink-0">
            <div class="flex items-center gap-2">
                <button
                    @click="runQuery"
                    :disabled="isLoading"
                    class="bg-emerald-500 text-zinc-950 px-4 h-8 rounded-sm font-black text-[11px] tracking-widest flex items-center gap-2 hover:bg-emerald-400 active:scale-95 transition-all disabled:opacity-50"
                >
                    <Icon v-if="isLoading" icon="tabler:loader-2" class="animate-spin text-lg" />
                    <Icon v-else icon="tabler:play" class="text-lg" />
                    RUN QUERY
                </button>
                <button
                    @click="clearQuery"
                    class="bg-zinc-900 text-zinc-400 border border-zinc-800 px-4 h-8 rounded-sm font-black text-[11px] tracking-widest flex items-center gap-2 hover:text-on-surface transition-all"
                >
                    <Icon icon="tabler:trash-x" class="text-lg" />
                    CLEAR
                </button>
            </div>

            <div class="flex items-center gap-4">
                <div v-if="result" class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest">
                    {{ result.data.length }} ROWS · {{ result.execution_time_ms }}ms
                </div>
                <button class="text-zinc-500 hover:text-on-surface transition-colors">
                    <Icon icon="tabler:settings" class="text-xl" />
                </button>
            </div>
        </div>

        <!-- Editor Area -->
        <div class="flex-1 flex flex-col min-h-0">
            <div class="flex-1 relative border-b border-zinc-900">
                <textarea
                    v-model="sql"
                    class="absolute inset-0 w-full h-full bg-zinc-950 p-6 font-mono text-sm text-emerald-400/90 resize-none outline-none focus:bg-zinc-950 transition-colors"
                    spellcheck="false"
                    placeholder="-- Write your SQL here..."
                ></textarea>
                <div class="absolute bottom-4 right-4 pointer-events-none opacity-20">
                    <Icon icon="tabler:brand-tabler" class="text-6xl text-emerald-400" />
                </div>
            </div>

            <!-- Results Area -->
            <div class="h-1/2 bg-zinc-950/50 flex flex-col overflow-hidden">
                <div class="h-8 border-b border-zinc-900 px-4 flex items-center bg-zinc-900/30">
                    <span class="text-[9px] font-black uppercase tracking-[0.2em] text-zinc-600">Query Results</span>
                </div>

                <div v-if="error" class="p-6">
                    <div class="bg-rose-500/10 border border-rose-500/20 p-4 rounded-sm">
                        <div class="flex items-center gap-2 text-rose-400 mb-1">
                            <Icon icon="tabler:alert-triangle" class="text-lg" />
                            <span class="text-[10px] font-black uppercase tracking-widest">Execution Error</span>
                        </div>
                        <p class="text-sm font-mono text-rose-300">{{ error }}</p>
                    </div>
                </div>

                <div v-else-if="result" class="flex-1 overflow-auto">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr class="border-b border-zinc-900 bg-zinc-900/20 sticky top-0">
                                <th
                                    v-for="col in result.columns"
                                    :key="col"
                                    class="px-4 py-2 text-[10px] font-black text-zinc-500 uppercase tracking-widest border-r border-zinc-900 last:border-0"
                                >
                                    {{ col }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(row, idx) in result.data"
                                :key="idx"
                                class="border-b border-zinc-900/50 hover:bg-emerald-400/5 transition-colors group"
                            >
                                <td
                                    v-for="col in result.columns"
                                    :key="col"
                                    class="px-4 py-2 text-xs font-mono text-zinc-400 border-r border-zinc-900/30 last:border-0 group-hover:text-zinc-200"
                                >
                                    {{ row[col] === null ? 'NULL' : row[col] }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="flex-1 flex items-center justify-center text-zinc-600 flex-col gap-2">
                    <Icon icon="tabler:brand-speedtest" class="text-4xl opacity-20" />
                    <p class="text-[10px] font-bold uppercase tracking-widest opacity-40">Ready to execute</p>
                </div>
            </div>
        </div>
    </div>
</template>
