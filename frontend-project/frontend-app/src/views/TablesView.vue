<script setup lang="ts">
import { Icon } from '@iconify/vue';

const tables = [
    { schema: 'public', name: 'users', count: 12482, active: true },
    { schema: 'public', name: 'profiles', count: 12482 },
    { schema: 'public', name: 'posts', count: 45210 },
    { schema: 'public', name: 'comments', count: 89000 },
    { schema: 'public', name: 'organizations', count: 420 },
];

const columns = [
    { name: 'id', type: 'uuid', nullable: false, default: 'uuid_generate_v4()', isPk: true },
    { name: 'email', type: 'text', nullable: false, default: 'NULL' },
    { name: 'full_name', type: 'text', nullable: true, default: 'NULL' },
    { name: 'created_at', type: 'timestamptz', nullable: false, default: 'now()' },
    { name: 'last_login', type: 'timestamptz', nullable: true, default: 'NULL' },
    { name: 'avatar_url', type: 'text', nullable: true, default: 'NULL' },
];
</script>

<template>
    <div class="flex-1 flex overflow-hidden bg-surface h-full">
        <!-- Tables Side Panel -->
        <aside class="w-72 border-r border-border flex flex-col shrink-0 bg-surface-low">
            <div class="p-4 border-b border-border">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-on-surface">Tables</h3>
                    <span class="text-[10px] bg-zinc-800 text-zinc-400 px-1.5 py-0.5 rounded font-bold">12 Total</span>
                </div>
                <div class="relative">
                    <input
                        class="w-full bg-zinc-950 border border-border rounded-sm text-xs px-8 py-2 focus:ring-emerald-400 focus:border-emerald-400 outline-none transition-all placeholder:text-zinc-600"
                        placeholder="Filter tables..."
                        type="text"
                    />
                    <Icon
                        icon="tabler:filter"
                        class="absolute left-2.5 top-1/2 -translate-y-1/2 text-sm text-zinc-500"
                    />
                </div>
            </div>

            <div class="flex-1 overflow-y-auto custom-scrollbar p-2">
                <div class="space-y-0.5">
                    <button
                        v-for="t in tables"
                        :key="t.name"
                        class="w-full flex items-center gap-2 px-3 py-2.5 rounded text-left text-xs transition-colors"
                        :class="[
                            t.active
                                ? 'bg-zinc-900 border border-emerald-400/20 text-emerald-400'
                                : 'text-zinc-400 hover:bg-zinc-900 hover:text-zinc-200',
                        ]"
                    >
                        <Icon icon="tabler:table" class="text-lg" />
                        {{ t.schema }}.{{ t.name }}
                    </button>

                    <div class="px-3 py-4">
                        <h4 class="text-[10px] text-zinc-600 font-bold uppercase tracking-widest mb-2">Internal</h4>
                        <button
                            class="w-full flex items-center gap-2 px-3 py-2.5 rounded text-left text-xs text-zinc-500 hover:bg-zinc-900 transition-colors"
                        >
                            <Icon icon="tabler:history" class="text-lg" />
                            auth.audit_log
                        </button>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Table Viewer Workspace -->
        <section class="flex-1 flex flex-col overflow-hidden">
            <!-- Table Header -->
            <header class="p-8 pb-4 flex items-end justify-between shrink-0">
                <div>
                    <div class="flex items-center gap-2 text-zinc-500 mb-1">
                        <span class="text-xs font-mono">public</span>
                        <Icon icon="tabler:chevron-right" class="text-xs" />
                    </div>
                    <h1 class="text-4xl font-black text-on-surface tracking-tight">users</h1>
                </div>
                <div class="flex gap-2">
                    <button
                        class="bg-surface-high border border-border px-4 py-2 rounded-sm text-xs font-bold hover:bg-zinc-800 transition-colors flex items-center gap-2"
                    >
                        <Icon icon="tabler:edit" class="text-lg" />
                        Edit Table
                    </button>
                    <button
                        class="bg-primary-container text-zinc-950 px-4 py-2 rounded-sm text-xs font-bold active:scale-[0.98] transition-transform flex items-center gap-2"
                    >
                        <Icon icon="tabler:column-insert-right" class="text-lg" />
                        New Column
                    </button>
                </div>
            </header>

            <!-- View Tabs -->
            <div class="px-8 border-b border-border flex gap-6 shrink-0">
                <button
                    class="border-b-2 border-emerald-400 text-emerald-400 pb-3 text-xs font-bold uppercase tracking-wider"
                >
                    Columns
                </button>
                <button
                    class="border-b-2 border-transparent text-zinc-500 hover:text-zinc-300 pb-3 text-xs font-bold uppercase tracking-wider"
                >
                    Indexes
                </button>
                <button
                    class="border-b-2 border-transparent text-zinc-500 hover:text-zinc-300 pb-3 text-xs font-bold uppercase tracking-wider"
                >
                    Foreign Keys
                </button>
                <button
                    class="border-b-2 border-transparent text-zinc-500 hover:text-zinc-300 pb-3 text-xs font-bold uppercase tracking-wider"
                >
                    Policies
                </button>
            </div>

            <!-- Table Grid Container -->
            <div class="flex-1 overflow-auto custom-scrollbar p-8">
                <div class="bg-surface border border-border rounded-sm shadow-2xl overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-low">
                                <th
                                    class="px-4 py-3 text-[10px] font-bold text-zinc-500 uppercase tracking-widest border-b border-border"
                                >
                                    Status
                                </th>
                                <th
                                    class="px-4 py-3 text-[10px] font-bold text-zinc-500 uppercase tracking-widest border-b border-border"
                                >
                                    Column Name
                                </th>
                                <th
                                    class="px-4 py-3 text-[10px] font-bold text-zinc-500 uppercase tracking-widest border-b border-border"
                                >
                                    Data Type
                                </th>
                                <th
                                    class="px-4 py-3 text-[10px] font-bold text-zinc-500 uppercase tracking-widest border-b border-border"
                                >
                                    Nullable
                                </th>
                                <th
                                    class="px-4 py-3 text-[10px] font-bold text-zinc-500 uppercase tracking-widest border-b border-border"
                                >
                                    Default Value
                                </th>
                                <th
                                    class="px-4 py-3 text-[10px] font-bold text-zinc-500 uppercase tracking-widest border-b border-border text-right"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="text-xs">
                            <tr
                                v-for="c in columns"
                                :key="c.name"
                                class="border-b border-border hover:bg-zinc-900/50 transition-colors group"
                            >
                                <td class="px-4 py-4">
                                    <div v-if="c.isPk" class="flex items-center gap-1.5">
                                        <span
                                            class="w-1.5 h-1.5 rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(0,229,153,0.4)]"
                                        ></span>
                                        <span class="text-[9px] font-bold text-emerald-400/80 uppercase">PK</span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 font-mono font-semibold text-on-surface">{{ c.name }}</td>
                                <td class="px-4 py-4">
                                    <span class="bg-zinc-800 px-2 py-0.5 rounded text-[10px] font-mono text-zinc-300">
                                        {{ c.type }}
                                    </span>
                                </td>
                                <td class="px-4 py-4" :class="[c.nullable ? 'text-emerald-400/80' : 'text-zinc-500']">
                                    {{ c.nullable ? 'Yes' : 'No' }}
                                </td>
                                <td class="px-4 py-4 font-mono text-zinc-500 italic">{{ c.default }}</td>
                                <td class="px-4 py-4 text-right">
                                    <button class="text-zinc-600 hover:text-emerald-400 transition-colors">
                                        <Icon icon="tabler:dots-vertical" class="text-lg" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Additional Info Sections -->
                <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Indexes Card -->
                    <div class="bg-surface border border-border rounded-sm p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-bold flex items-center gap-2">
                                <Icon icon="tabler:bolt" class="text-emerald-400 text-lg" />
                                Active Indexes
                            </h3>
                            <button
                                class="text-[10px] font-bold text-emerald-400 uppercase tracking-widest hover:underline"
                            >
                                Manage
                            </button>
                        </div>
                        <div class="space-y-2">
                            <div
                                class="flex items-center justify-between p-3 bg-surface-low border border-border rounded-sm"
                            >
                                <div>
                                    <p class="text-xs font-mono text-on-surface">users_pkey</p>
                                    <p class="text-[10px] text-zinc-500">PRIMARY KEY (id)</p>
                                </div>
                                <span
                                    class="bg-emerald-400/10 text-emerald-400 text-[9px] px-2 py-0.5 rounded border border-emerald-400/20"
                                >
                                    UNIQUE
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Foreign Keys Card -->
                    <div class="bg-surface border border-border rounded-sm p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-bold flex items-center gap-2 text-on-surface">
                                <Icon icon="tabler:link" class="text-emerald-400 text-lg" />
                                Foreign Keys
                            </h3>
                            <button
                                class="text-[10px] font-bold text-emerald-400 uppercase tracking-widest hover:underline"
                            >
                                Visualize
                            </button>
                        </div>
                        <div
                            class="flex items-center justify-center h-24 border border-dashed border-border rounded-sm"
                        >
                            <div class="text-center">
                                <p class="text-xs text-zinc-500">No outgoing foreign keys defined</p>
                                <p class="text-[10px] text-zinc-600 mt-1">This table does not reference other tables</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contextual Inspector (Right Panel) -->
        <aside class="w-80 border-l border-border bg-surface-low hidden 2xl:flex flex-col">
            <div class="p-6">
                <h3 class="text-[10px] font-bold uppercase tracking-widest text-zinc-500 mb-6">Table Statistics</h3>
                <div class="space-y-6">
                    <div class="bg-zinc-950 p-4 rounded-sm border border-border">
                        <p class="text-[10px] text-zinc-500 uppercase font-bold mb-1">Rows Estimated</p>
                        <p class="text-2xl font-bold text-on-surface">12,482</p>
                        <div class="mt-2 h-1 w-full bg-zinc-800 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-400 w-3/4"></div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-zinc-950 p-3 rounded-sm border border-border">
                            <p class="text-[10px] text-zinc-500 uppercase font-bold mb-1">Size</p>
                            <p class="text-lg font-bold text-on-surface">1.2 MB</p>
                        </div>
                        <div class="bg-zinc-950 p-3 rounded-sm border border-border">
                            <p class="text-[10px] text-zinc-500 uppercase font-bold mb-1">Index Size</p>
                            <p class="text-lg font-bold text-on-surface">456 KB</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-auto p-6 border-t border-border">
                <button
                    class="w-full flex items-center justify-between text-xs text-rose-400 hover:bg-rose-400/10 p-2 rounded-sm transition-colors group"
                >
                    <span class="font-bold">Delete Table</span>
                    <Icon icon="tabler:trash" class="text-lg" />
                </button>
            </div>
        </aside>
    </div>
</template>
