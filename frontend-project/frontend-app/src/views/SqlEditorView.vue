<script setup lang="ts">
import { ref } from 'vue';
import { Icon } from '@iconify/vue';

const sqlQuery = ref(`SELECT
  u.id,
  u.name,
  SUM(o.total_value) AS value
FROM users u
JOIN orders o ON u.id = o.user_id
GROUP BY 1, 2;`);

const results = [
    { id: '#8291', name: 'Alex Rivera', value: '$12,450.00', last_order: '2023-11-20', status: 'ACTIVE' },
    { id: '#8292', name: 'Jordan Smith', value: '$8,210.50', last_order: '2023-11-18', status: 'ACTIVE' },
    { id: '#8293', name: 'Samantha Lee', value: '$5,100.00', last_order: '2023-11-15', status: 'INACTIVE' },
    { id: '#8294', name: 'Marcus Thorne', value: '$21,800.20', last_order: '2023-11-21', status: 'ACTIVE' },
];
</script>

<template>
    <div class="flex-1 flex flex-col min-w-0 bg-surface h-full">
        <!-- Sub-Header / Actions -->
        <div class="h-12 border-b border-border flex items-center justify-between px-4 bg-surface-low">
            <div class="flex items-center gap-2">
                <button
                    class="bg-primary-container text-zinc-950 px-4 h-8 rounded-sm font-bold text-[11px] tracking-widest flex items-center gap-2 hover:opacity-90 active:scale-95 transition-all"
                >
                    <Icon icon="tabler:play" class="text-lg" />
                    RUN
                </button>
                <button
                    class="border border-border text-on-surface px-4 h-8 rounded-sm font-bold text-[11px] tracking-widest flex items-center gap-2 hover:bg-surface-high transition-colors"
                >
                    <Icon icon="tabler:info-circle" class="text-lg" />
                    EXPLAIN
                </button>
                <button
                    class="border border-border text-on-surface px-4 h-8 rounded-sm font-bold text-[11px] tracking-widest flex items-center gap-2 hover:bg-surface-high transition-colors"
                >
                    <Icon icon="tabler:chart-arrows" class="text-lg" />
                    ANALYZE
                </button>
            </div>

            <div class="flex items-center gap-4">
                <div
                    class="flex items-center gap-1 text-on-surface-variant text-[11px] font-bold uppercase tracking-wider"
                >
                    <Icon icon="tabler:database" class="text-lg" />
                    PROD_DB
                </div>
                <span class="w-px h-4 bg-border"></span>
                <button class="text-on-surface-variant hover:text-primary transition-colors">
                    <Icon icon="tabler:device-floppy" class="text-xl" />
                </button>
                <button class="text-on-surface-variant hover:text-primary transition-colors">
                    <Icon icon="tabler:share" class="text-xl" />
                </button>
            </div>
        </div>

        <!-- Content Workspace -->
        <div class="flex flex-grow overflow-hidden">
            <!-- Editor Area -->
            <div class="flex-grow flex flex-col min-w-0">
                <div class="flex-grow flex bg-zinc-950 relative overflow-hidden">
                    <!-- Line Numbers (Static for now) -->
                    <div
                        class="w-12 bg-zinc-900/50 flex flex-col items-end py-4 pr-3 text-zinc-600 font-mono text-xs border-r border-border select-none"
                    >
                        <span>1</span>
                        <span>2</span>
                        <span>3</span>
                        <span class="text-emerald-400">4</span>
                        <span>5</span>
                        <span>6</span>
                        <span>7</span>
                    </div>
                    <!-- Code Editor Placeholder -->
                    <textarea
                        v-model="sqlQuery"
                        class="flex-grow p-4 font-mono text-sm bg-transparent border-none focus:ring-0 text-slate-300 resize-none overflow-auto custom-scrollbar"
                        spellcheck="false"
                    ></textarea>
                    <div class="absolute bottom-4 right-4 text-zinc-600 text-[10px] font-mono opacity-50">
                        UTF-8 | PostgreSQL
                    </div>
                </div>

                <!-- Resizer -->
                <div class="h-1 bg-border cursor-ns-resize hover:bg-primary transition-colors"></div>

                <!-- Results Area -->
                <div class="h-72 flex flex-col bg-surface overflow-hidden">
                    <div class="h-10 border-b border-border flex items-center px-4 justify-between bg-surface-high">
                        <div class="flex items-center gap-4">
                            <span class="text-[10px] font-bold tracking-widest text-primary uppercase">
                                RESULTS (124 ROWS)
                            </span>
                            <span class="text-[10px] font-bold tracking-widest text-on-surface-variant uppercase">
                                EXECUTION: 14MS
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button class="text-on-surface-variant hover:text-on-surface">
                                <Icon icon="tabler:download" class="text-lg" />
                            </button>
                            <button class="text-on-surface-variant hover:text-on-surface">
                                <Icon icon="tabler:filter" class="text-lg" />
                            </button>
                        </div>
                    </div>
                    <div class="overflow-auto flex-grow custom-scrollbar">
                        <table class="w-full text-left border-collapse">
                            <thead
                                class="sticky top-0 bg-surface-high text-on-surface-variant text-[10px] font-bold uppercase tracking-widest"
                            >
                                <tr>
                                    <th class="px-4 py-2 border-b border-border">id</th>
                                    <th class="px-4 py-2 border-b border-border">name</th>
                                    <th class="px-4 py-2 border-b border-border">value</th>
                                    <th class="px-4 py-2 border-b border-border">last_order</th>
                                    <th class="px-4 py-2 border-b border-border">status</th>
                                </tr>
                            </thead>
                            <tbody class="text-on-surface divide-y divide-border">
                                <tr
                                    v-for="r in results"
                                    :key="r.id"
                                    class="hover:bg-surface-high transition-colors text-xs"
                                >
                                    <td class="px-4 py-2 font-mono text-on-secondary-container">{{ r.id }}</td>
                                    <td class="px-4 py-2">{{ r.name }}</td>
                                    <td class="px-4 py-2">{{ r.value }}</td>
                                    <td class="px-4 py-2 text-on-surface-variant">{{ r.last_order }}</td>
                                    <td class="px-4 py-2">
                                        <span class="flex items-center gap-2">
                                            <span
                                                class="w-2 h-2 rounded-full"
                                                :class="[
                                                    r.status === 'ACTIVE'
                                                        ? 'bg-emerald-400 shadow-[0_0_8px_rgba(0,229,153,0.4)]'
                                                        : 'bg-zinc-600',
                                                ]"
                                            ></span>
                                            {{ r.status }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Side Panel (History/Saved) -->
            <div class="w-80 bg-surface-low border-l border-border flex flex-col shrink-0 hidden lg:flex">
                <!-- Tabs -->
                <div class="flex border-b border-border">
                    <button
                        class="flex-1 py-3 text-[10px] font-bold tracking-widest text-primary border-b-2 border-primary bg-surface-high"
                    >
                        SAVED
                    </button>
                    <button
                        class="flex-1 py-3 text-[10px] font-bold tracking-widest text-on-surface-variant hover:text-on-surface transition-colors"
                    >
                        HISTORY
                    </button>
                </div>

                <!-- Tab Content -->
                <div class="p-4 space-y-4 overflow-y-auto custom-scrollbar flex-grow">
                    <div class="relative">
                        <Icon
                            icon="tabler:search"
                            class="absolute left-3 top-1/2 -translate-y-1/2 text-lg text-on-surface-variant"
                        />
                        <input
                            class="w-full bg-zinc-950 border-none rounded-sm pl-10 text-xs py-2 focus:ring-1 focus:ring-primary placeholder:text-zinc-600"
                            placeholder="Filter queries..."
                            type="text"
                        />
                    </div>

                    <div class="space-y-3">
                        <div
                            v-for="i in 3"
                            :key="i"
                            class="p-3 bg-surface-high rounded border border-border hover:border-primary/50 cursor-pointer group transition-all"
                        >
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-xs font-bold text-on-surface truncate pr-2">Monthly User Growth</span>
                                <Icon icon="tabler:star-filled" class="text-emerald-400" />
                            </div>
                            <div class="text-zinc-500 font-mono text-[10px] line-clamp-2">
                                SELECT date_trunc('month', created_at) as month, count(*) FROM users...
                            </div>
                            <div class="mt-2 flex items-center justify-between">
                                <span class="text-[9px] text-on-surface-variant uppercase font-bold tracking-widest">
                                    PostgreSQL
                                </span>
                                <span class="text-[9px] text-on-surface-variant">2d ago</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel Footer -->
                <div class="mt-auto p-4 border-t border-border bg-surface-low">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-emerald-400/10 rounded">
                            <Icon icon="tabler:bolt" class="text-emerald-400 text-xl" />
                        </div>
                        <div>
                            <div class="text-[10px] text-on-surface font-bold uppercase tracking-widest">
                                QUERY PERFORMANCE
                            </div>
                            <div class="text-[10px] text-on-surface-variant">Optimized by assistant</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
