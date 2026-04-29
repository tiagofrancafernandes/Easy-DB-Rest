<script setup lang="ts">
import { ref, inject } from 'vue';
import { Icon } from '@iconify/vue';

const connectionId = inject('connectionId') as string;
const isLoading = ref(false);

const queryGroups = ref([
    {
        id: 1,
        type: 'WHERE',
        conditions: [
            { column: 'id', operator: '>', value: '100' },
            { column: 'status', operator: '=', value: "'ACTIVE'" },
        ],
    },
]);

const operators = ['=', '>', '<', '>=', '<=', '!=', 'LIKE', 'IN', 'IS NULL', 'IS NOT NULL'];
</script>

<template>
    <div class="h-full flex flex-col bg-surface overflow-hidden">
        <div class="h-12 border-b border-zinc-900 bg-surface-low flex items-center justify-between px-4 shrink-0">
            <div class="flex items-center gap-2">
                <Icon icon="tabler:poker-chip" class="text-emerald-400 text-xl" />
                <h1 class="text-[11px] font-black uppercase tracking-widest text-on-surface">
                    Declarative Query Builder
                </h1>
            </div>
            <button
                class="bg-primary-container text-zinc-950 px-4 h-8 rounded-sm font-black text-[11px] tracking-widest flex items-center gap-2 hover:opacity-90 active:scale-95 transition-all"
            >
                <Icon icon="tabler:search" class="text-lg" />
                PREVIEW DATA
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-8 bg-zinc-950/20">
            <div class="max-w-4xl mx-auto space-y-8">
                <!-- Select Section -->
                <section class="bg-surface border border-zinc-900 p-6 rounded-sm shadow-xl">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-2 h-6 bg-emerald-500 rounded-full"></div>
                        <h2 class="text-sm font-black text-on-surface uppercase tracking-widest">Select Columns</h2>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <div
                            class="px-3 py-1.5 bg-zinc-900 border border-emerald-400/30 rounded text-xs text-emerald-400 flex items-center gap-2"
                        >
                            <Icon icon="tabler:asterisk" />
                            All Columns (*)
                            <button class="hover:text-white"><Icon icon="tabler:x" /></button>
                        </div>
                        <button
                            class="px-3 py-1.5 bg-zinc-900 border border-zinc-800 rounded text-xs text-zinc-500 hover:text-on-surface transition-all border-dashed"
                        >
                            + Add Column
                        </button>
                    </div>
                </section>

                <!-- Conditions Section -->
                <section class="bg-surface border border-zinc-900 p-6 rounded-sm shadow-xl">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-6 bg-blue-500 rounded-full"></div>
                            <h2 class="text-sm font-black text-on-surface uppercase tracking-widest">
                                Conditions (WHERE)
                            </h2>
                        </div>
                        <button
                            class="text-[10px] font-bold text-zinc-600 hover:text-blue-400 transition-colors uppercase tracking-widest"
                        >
                            + Add Group
                        </button>
                    </div>

                    <div
                        v-for="group in queryGroups"
                        :key="group.id"
                        class="space-y-3 bg-zinc-900/30 p-4 rounded-sm border border-zinc-900/50"
                    >
                        <div v-for="(cond, idx) in group.conditions" :key="idx" class="flex gap-3 items-center">
                            <input
                                v-model="cond.column"
                                type="text"
                                class="flex-1 bg-zinc-950 border border-zinc-800 rounded-sm px-3 py-1.5 text-xs text-zinc-300 outline-none focus:border-blue-500/50"
                                placeholder="column_name"
                            />
                            <select
                                v-model="cond.operator"
                                class="w-24 bg-zinc-950 border border-zinc-800 rounded-sm px-2 py-1.5 text-xs text-zinc-500 outline-none focus:border-blue-500/50 appearance-none text-center"
                            >
                                <option v-for="op in operators" :key="op" :value="op">{{ op }}</option>
                            </select>
                            <input
                                v-model="cond.value"
                                type="text"
                                class="flex-1 bg-zinc-950 border border-zinc-800 rounded-sm px-3 py-1.5 text-xs text-zinc-300 outline-none focus:border-blue-500/50"
                                placeholder="'value'"
                            />
                            <button class="p-1.5 text-zinc-700 hover:text-rose-400 transition-colors">
                                <Icon icon="tabler:trash" />
                            </button>
                        </div>
                        <button
                            class="text-[10px] font-bold text-zinc-600 hover:text-zinc-400 transition-colors uppercase tracking-widest pt-2"
                        >
                            + Add Condition
                        </button>
                    </div>
                </section>

                <!-- Order/Limit -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <section class="bg-surface border border-zinc-900 p-6 rounded-sm shadow-xl">
                        <h2 class="text-[10px] font-black text-zinc-500 uppercase tracking-widest mb-4">Sorting</h2>
                        <button
                            class="w-full py-4 border border-dashed border-zinc-800 rounded text-xs text-zinc-600 hover:border-zinc-700 transition-all"
                        >
                            + Add Sort Rule
                        </button>
                    </section>
                    <section class="bg-surface border border-zinc-900 p-6 rounded-sm shadow-xl">
                        <h2 class="text-[10px] font-black text-zinc-500 uppercase tracking-widest mb-4">Limit</h2>
                        <input
                            type="number"
                            class="w-full bg-zinc-950 border border-zinc-800 rounded-sm px-4 py-2 text-sm text-zinc-400 outline-none"
                            value="100"
                        />
                    </section>
                </div>
            </div>
        </div>

        <!-- Footer / Generated SQL -->
        <div class="h-16 border-t border-zinc-900 bg-surface-low flex items-center justify-between px-6 shrink-0">
            <div class="flex items-center gap-3">
                <span class="text-[9px] font-black text-zinc-600 uppercase tracking-widest">Generated JSON</span>
                <div
                    class="px-3 py-1 bg-zinc-950 rounded text-[10px] font-mono text-emerald-400/60 border border-zinc-900"
                >
                    { "select": ["*"], "where": [ ... ] }
                </div>
            </div>
            <div class="flex gap-4">
                <button
                    class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest hover:text-white transition-colors"
                >
                    Export SQL
                </button>
                <button
                    class="bg-emerald-500 text-zinc-950 px-6 py-2 rounded-sm font-black text-xs tracking-widest hover:opacity-90 active:scale-95 transition-all"
                >
                    GENERATE RESULTS
                </button>
            </div>
        </div>
    </div>
</template>
