<script setup lang="ts">
import { inject, onMounted } from 'vue';
import { Icon } from '@iconify/vue';
import { useDatabase } from '@/composables/useDatabase';
import { useRouter } from 'vue-router';

const connectionId = inject('connectionId') as string;
const { tables, fetchTables, executeQuery, isLoading } = useDatabase(connectionId);
const router = useRouter();

onMounted(fetchTables);

const handleDeleteTable = async (tableName: string) => {
    if (confirm(`Are you sure you want to drop table "${tableName}"? This action is irreversible.`)) {
        try {
            await executeQuery(`DROP TABLE ${tableName}`);
            await fetchTables();
        } catch (e: any) {
            alert(`Failed to delete table: ${e.message}`);
        }
    }
};
</script>

<template>
    <div class="p-8 h-full overflow-y-auto">
        <div class="max-w-6xl mx-auto">
            <div class="flex justify-between items-end mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-on-surface tracking-tight">Tables</h1>
                    <p class="text-zinc-500 text-sm mt-1">Manage schemas and table structures.</p>
                </div>
                <div class="flex gap-3">
                    <button
                        class="bg-zinc-900 text-zinc-400 border border-zinc-800 px-4 py-2 rounded-sm font-bold text-xs tracking-widest hover:text-on-surface transition-all flex items-center gap-2"
                    >
                        <Icon icon="tabler:plus" class="text-base" />
                        NEW SCHEMA
                    </button>
                    <button
                        class="bg-primary-container text-zinc-950 px-4 py-2 rounded-sm font-bold text-xs tracking-widest hover:opacity-90 transition-all flex items-center gap-2"
                    >
                        <Icon icon="tabler:table-plus" class="text-base" />
                        NEW TABLE
                    </button>
                </div>
            </div>

            <div v-if="isLoading && !tables.length" class="flex justify-center py-20">
                <Icon icon="tabler:loader-2" class="text-3xl text-emerald-400 animate-spin" />
            </div>

            <div v-else class="bg-surface border border-zinc-900 rounded-sm overflow-hidden">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="bg-zinc-900/40 border-b border-zinc-900">
                            <th class="px-6 py-3 text-[10px] font-black text-zinc-600 uppercase tracking-widest">
                                Table Name
                            </th>
                            <th class="px-6 py-3 text-[10px] font-black text-zinc-600 uppercase tracking-widest">
                                Schema
                            </th>
                            <th class="px-6 py-3 text-[10px] font-black text-zinc-600 uppercase tracking-widest">
                                Est. Rows
                            </th>
                            <th
                                class="px-6 py-3 text-[10px] font-black text-zinc-600 uppercase tracking-widest text-right"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="table in tables"
                            :key="table.name"
                            class="border-b border-zinc-900/50 hover:bg-zinc-900/30 transition-colors group cursor-pointer"
                            @click="router.push(`/c/${connectionId}/tables/${table.name}`)"
                        >
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <Icon
                                        icon="tabler:table"
                                        class="text-zinc-700 group-hover:text-emerald-400 transition-colors"
                                    />
                                    <span class="text-sm font-bold text-zinc-300 group-hover:text-on-surface">
                                        {{ table.name }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-xs font-mono text-zinc-500">{{ table.schema || 'public' }}</td>
                            <td class="px-6 py-4 text-xs text-zinc-500">{{ table.rows?.toLocaleString() || '-' }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <button 
                                        @click.stop="handleDeleteTable(table.name)"
                                        class="p-1.5 text-zinc-600 hover:text-rose-400 transition-colors"
                                        title="Delete Table"
                                    >
                                        <Icon icon="tabler:trash" />
                                    </button>
                                    <button class="p-1.5 text-zinc-600 hover:text-emerald-400 transition-colors">
                                        <Icon icon="tabler:external-link" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
