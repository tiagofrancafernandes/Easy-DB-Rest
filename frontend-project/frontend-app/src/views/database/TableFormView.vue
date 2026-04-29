<script setup lang="ts">
import { inject, ref } from 'vue';
import { useRouter } from 'vue-router';
import { Icon } from '@iconify/vue';
import { useDatabase } from '@/composables/useDatabase';
import { useToast } from '@/composables/useToast';

const connectionId = inject('connectionId') as string;
const { fetchTables } = useDatabase(connectionId);
const router = useRouter();
const toast = useToast();

const tableName = ref('');
const columns = ref([
    { name: 'id', type: 'id', nullable: false, primary: true },
    { name: 'created_at', type: 'timestamp', nullable: true, primary: false },
]);

const columnTypes = ['id', 'string', 'integer', 'text', 'boolean', 'timestamp', 'date', 'decimal', 'json'];

const addColumn = () => {
    columns.value.push({ name: '', type: 'string', nullable: true, primary: false });
};

const removeColumn = (index: number) => {
    if (columns.value.length > 1) {
        columns.value.splice(index, 1);
    }
};

const isLoading = ref(false);

const handleCreate = async () => {
    if (!tableName.value) {
        toast.error('Table name is required');
        return;
    }

    isLoading.value = true;
    try {
        const { apiClient } = await import('@/api/client');
        await apiClient(`/connections/${connectionId}/tables`, {
            method: 'POST',
            body: JSON.stringify({
                table: tableName.value,
                columns: columns.value,
            }),
        });

        toast.success(`Table ${tableName.value} created successfully`);
        await fetchTables();
        router.push(`/c/${connectionId}/tables/${tableName.value}`);
    } catch (e: any) {
        toast.error(`Failed to create table: ${e.message}`);
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
    <div class="h-full flex flex-col bg-background overflow-hidden">
        <header
            class="h-14 flex items-center justify-between px-6 border-b border-zinc-800 bg-zinc-950/80 backdrop-blur-md shrink-0"
        >
            <div class="flex items-center gap-3">
                <button
                    @click="router.back()"
                    class="p-2 hover:bg-zinc-900 rounded text-zinc-500 hover:text-zinc-200 transition-all"
                >
                    <Icon icon="tabler:arrow-left" class="text-xl" />
                </button>
                <h1 class="text-sm font-bold text-on-surface tracking-tight uppercase">New Table</h1>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-8 custom-scrollbar">
            <div class="max-w-4xl mx-auto space-y-8">
                <!-- Table Name -->
                <section class="space-y-4">
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-zinc-500">
                        Table Name
                    </label>
                    <input
                        v-model="tableName"
                        type="text"
                        placeholder="e.g. users, products, orders"
                        class="w-full bg-zinc-900 border border-zinc-800 rounded px-4 py-3 text-sm text-on-surface focus:border-emerald-500/50 outline-none transition-all placeholder:text-zinc-700"
                    />
                </section>

                <!-- Columns Section -->
                <section class="space-y-4">
                    <div class="flex justify-between items-center">
                        <label class="text-[10px] font-black uppercase tracking-[0.2em] text-zinc-500">Columns</label>
                        <button
                            @click="addColumn"
                            class="flex items-center gap-2 px-3 py-1.5 bg-zinc-900 hover:bg-zinc-800 text-emerald-400 border border-zinc-800 rounded text-[10px] font-black uppercase tracking-widest transition-all"
                        >
                            <Icon icon="tabler:plus" />
                            Add Column
                        </button>
                    </div>

                    <div class="bg-zinc-900 border border-zinc-800 rounded overflow-hidden">
                        <table class="w-full border-collapse text-left text-xs">
                            <thead>
                                <tr class="bg-zinc-950 border-b border-zinc-800">
                                    <th class="px-4 py-2 font-bold text-zinc-600">Name</th>
                                    <th class="px-4 py-2 font-bold text-zinc-600">Type</th>
                                    <th class="px-4 py-2 font-bold text-zinc-600 text-center">PK</th>
                                    <th class="px-4 py-2 font-bold text-zinc-600 text-center">Null</th>
                                    <th class="px-4 py-2 font-bold text-zinc-600 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-800/50">
                                <tr v-for="(col, idx) in columns" :key="idx" class="group">
                                    <td class="p-2">
                                        <input
                                            v-model="col.name"
                                            class="w-full bg-zinc-950 border border-zinc-800 rounded px-2 py-1.5 focus:border-emerald-400/50 outline-none transition-all"
                                            placeholder="Column name"
                                        />
                                    </td>
                                    <td class="p-2">
                                        <select
                                            v-model="col.type"
                                            class="w-full bg-zinc-950 border border-zinc-800 rounded px-2 py-1.5 focus:border-emerald-400/50 outline-none transition-all"
                                        >
                                            <option v-for="t in columnTypes" :key="t" :value="t">{{ t }}</option>
                                        </select>
                                    </td>
                                    <td class="p-2 text-center">
                                        <input
                                            type="checkbox"
                                            v-model="col.primary"
                                            class="rounded border-zinc-800 bg-zinc-950 text-emerald-500"
                                        />
                                    </td>
                                    <td class="p-2 text-center">
                                        <input
                                            type="checkbox"
                                            v-model="col.nullable"
                                            class="rounded border-zinc-800 bg-zinc-950 text-emerald-500"
                                        />
                                    </td>
                                    <td class="p-2 text-right">
                                        <button
                                            @click="removeColumn(idx)"
                                            class="p-2 text-zinc-700 hover:text-rose-500 transition-all"
                                            :disabled="columns.length === 1"
                                        >
                                            <Icon icon="tabler:trash" />
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Actions -->
                <div class="pt-6 border-t border-zinc-800 flex justify-end gap-4">
                    <button
                        @click="router.back()"
                        class="px-6 py-2 rounded text-xs font-black uppercase tracking-widest text-zinc-500 hover:text-zinc-200 transition-all"
                    >
                        Cancel
                    </button>
                    <button
                        @click="handleCreate"
                        :disabled="isLoading"
                        class="px-8 py-2 bg-emerald-500 hover:bg-emerald-400 text-zinc-950 rounded text-xs font-black uppercase tracking-[0.2em] transition-all disabled:opacity-50"
                    >
                        <span v-if="isLoading">Creating...</span>
                        <span v-else>Create Table</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
