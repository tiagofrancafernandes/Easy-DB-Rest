<script setup lang="ts">
import { inject, onMounted, ref, watch, computed, type ComputedRef } from 'vue';
import { Icon } from '@iconify/vue';
import { useDatabase, type Column } from '@/composables/useDatabase';
import { useToast } from '@/composables/useToast';

const props = defineProps<{
    tableName: string;
}>();

const connectionId = inject('connectionId') as ComputedRef<string>;
const dbHelper = computed(() => useDatabase(connectionId.value));
const toast = useToast();

const activeTab = ref<'data' | 'structure'>('data');
const columns = ref<Column[]>([]);
const tableData = ref<any[]>([]);
const totalRows = ref(0);
const limit = ref(50);
const offset = ref(0);

// Proxy computed properties for convenience
const isLoading = computed(() => dbHelper.value.isLoading.value);

// Editing state
const isAddingRecord = ref(false);
const newRecord = ref<Record<string, any>>({});
const modifiedRows = ref<Record<number, Record<string, any>>>({});
const editingCell = ref<{ row: number; col: string } | null>(null);

const loadTableInfo = async () => {
    try {
        const response = await dbHelper.value.fetchTableDetails(props.tableName);
        columns.value = response.data.columns;
        
        if (activeTab.value === 'data') {
            await loadData();
        }
    } catch (e: any) {
        toast.error(`Failed to load table info: ${e.message}`);
    }
};

const loadData = async () => {
    try {
        const response = await dbHelper.value.fetchTableData(props.tableName, { 
            limit: limit.value, 
            offset: offset.value 
        });
        tableData.value = response.data;
        totalRows.value = response.meta?.total || 0;
        modifiedRows.value = {};
    } catch (e: any) {
        toast.error(`Failed to load data: ${e.message}`);
    }
};

onMounted(loadTableInfo);
watch(() => props.tableName, loadTableInfo);
watch(connectionId, loadTableInfo); // Refresh when connection changes
watch([limit, offset, activeTab], () => {
    if (activeTab.value === 'data') loadData();
});

const startAddRecord = () => {
    isAddingRecord.value = true;
    newRecord.value = {};
    columns.value.forEach(col => {
        newRecord.value[col.name] = col.default || null;
    });
};

const saveNewRecord = async () => {
    try {
        await dbHelper.value.insertRecord(props.tableName, newRecord.value);
        toast.success('Record inserted successfully');
        isAddingRecord.value = false;
        await loadData();
    } catch (e: any) {
        toast.error(`Failed to save record: ${e.message}`);
    }
};

const getPK = (row: any) => {
    const pk: Record<string, any> = {};
    const pkCols = columns.value.filter(c => c.isPk);
    if (pkCols.length > 0) {
        pkCols.forEach(c => pk[c.name] = row[c.name]);
    } else {
        // Fallback to all columns if no PK (risky but sometimes necessary)
        columns.value.forEach(c => pk[c.name] = row[c.name]);
    }
    return pk;
};

const saveChanges = async () => {
    const indices = Object.keys(modifiedRows.value).map(Number);
    let successCount = 0;
    
    for (const idx of indices) {
        try {
            const originalRow = tableData.value[idx];
            const updatedData = modifiedRows.value[idx];
            const pk = getPK(originalRow);
            
            await dbHelper.value.updateRecord(props.tableName, pk, updatedData);
            successCount++;
        } catch (e: any) {
            toast.error(`Failed to update row ${idx + 1}: ${e.message}`);
        }
    }
    
    if (successCount > 0) {
        toast.success(`${successCount} record(s) updated`);
        await loadData();
    }
};

const handleDelete = async (row: any) => {
    if (!confirm('Are you sure you want to delete this record?')) return;
    
    try {
        const pk = getPK(row);
        await dbHelper.value.deleteRecord(props.tableName, pk);
        toast.success('Record deleted');
        await loadData();
    } catch (e: any) {
        toast.error(`Failed to delete: ${e.message}`);
    }
};

const discardChanges = () => {
    modifiedRows.value = {};
    isAddingRecord.value = false;
    toast.info('Changes discarded');
};

const handleCellEdit = (rowIndex: number, colName: string, value: any) => {
    if (!modifiedRows.value[rowIndex]) {
        modifiedRows.value[rowIndex] = { ...tableData.value[rowIndex] };
    }
    modifiedRows.value[rowIndex][colName] = value;
};

const hasChanges = computed(() => Object.keys(modifiedRows.value).length > 0 || isAddingRecord.value);

</script>

<template>
    <div class="h-full flex flex-col bg-background overflow-hidden">
        <!-- Header -->
        <header class="h-14 flex items-center justify-between px-6 border-b border-zinc-800 bg-zinc-950/80 backdrop-blur-md shrink-0">
            <div class="flex items-center gap-6">
                <div class="flex items-center gap-3">
                    <Icon icon="tabler:table" class="text-xl text-emerald-400" />
                    <h1 class="text-sm font-bold text-on-surface tracking-tight">{{ tableName }}</h1>
                </div>
                
                <nav class="flex items-center gap-6">
                    <button
                        @click="activeTab = 'data'"
                        class="text-sm tracking-tight transition-colors pb-1"
                        :class="[activeTab === 'data' ? 'text-emerald-400 font-semibold border-b-2 border-emerald-400' : 'text-zinc-500 hover:text-zinc-200']"
                    >Data</button>
                    <button
                        @click="activeTab = 'structure'"
                        class="text-sm tracking-tight transition-colors pb-1"
                        :class="[activeTab === 'structure' ? 'text-emerald-400 font-semibold border-b-2 border-emerald-400' : 'text-zinc-500 hover:text-zinc-200']"
                    >Structure</button>
                </nav>
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2 px-3 py-1 bg-zinc-900 border border-zinc-800 rounded">
                    <span class="text-[10px] text-zinc-500 font-black uppercase">Rows</span>
                    <span class="text-xs text-white font-mono">{{ totalRows.toLocaleString() }}</span>
                </div>
                <Icon v-if="isLoading" icon="tabler:loader-2" class="animate-spin text-emerald-400" />
            </div>
        </header>

        <!-- Toolbar -->
        <div class="flex items-center justify-between px-6 py-3 border-b border-zinc-800 bg-zinc-900/30 shrink-0">
            <div class="flex items-center gap-3">
                <button
                    @click="startAddRecord"
                    class="flex items-center gap-2 px-3 py-1.5 bg-zinc-800 hover:bg-zinc-700 text-white rounded text-xs transition-all active:scale-95"
                    :disabled="isAddingRecord"
                >
                    <Icon icon="tabler:plus" class="text-base" />
                    <span>Add Record</span>
                </button>
                
                <div class="h-6 w-px bg-zinc-800"></div>
                
                <button
                    v-if="hasChanges"
                    @click="isAddingRecord ? saveNewRecord() : saveChanges()"
                    class="flex items-center gap-2 px-3 py-1.5 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/20 rounded text-xs transition-all active:scale-95 font-medium animate-in fade-in zoom-in duration-200"
                >
                    <Icon icon="tabler:save" class="text-base" />
                    <span>{{ isAddingRecord ? 'Save Record' : 'Save Changes' }}</span>
                </button>
                
                <button
                    v-if="hasChanges"
                    @click="discardChanges"
                    class="flex items-center gap-2 px-3 py-1.5 bg-zinc-900 hover:bg-zinc-800 text-zinc-400 rounded text-xs transition-all active:scale-95 animate-in fade-in zoom-in duration-200"
                >
                    <Icon icon="tabler:x" class="text-base text-rose-500" />
                    <span>Discard</span>
                </button>
            </div>

            <div class="flex items-center gap-4 text-xs text-zinc-500">
                <div class="flex items-center gap-2">
                    <span>Show</span>
                    <select
                        v-model="limit"
                        class="bg-zinc-900 border border-zinc-800 rounded px-2 py-0.5 text-zinc-300 focus:outline-none focus:border-zinc-700 outline-none"
                    >
                        <option :value="50">50</option>
                        <option :value="100">100</option>
                        <option :value="500">500</option>
                    </select>
                </div>
                <div class="flex items-center gap-1 border border-zinc-800 rounded overflow-hidden">
                    <button
                        @click="offset = Math.max(0, offset - limit)"
                        class="p-1 hover:bg-zinc-800 border-r border-zinc-800 text-zinc-600 disabled:opacity-30"
                        :disabled="offset === 0"
                    >
                        <Icon icon="tabler:chevron-left" />
                    </button>
                    <span class="px-3 text-[10px] font-mono">
                        {{ offset + 1 }} - {{ Math.min(offset + limit, totalRows) }} / {{ totalRows }}
                    </span>
                    <button
                        @click="offset = offset + limit"
                        class="p-1 hover:bg-zinc-800 border-l border-zinc-800 text-zinc-400 disabled:opacity-30"
                        :disabled="offset + limit >= totalRows"
                    >
                        <Icon icon="tabler:chevron-right" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto custom-scrollbar bg-zinc-950">
            <!-- Data Grid -->
            <table v-if="activeTab === 'data'" class="w-full border-collapse text-left font-mono text-xs">
                <thead class="sticky top-0 z-20 bg-zinc-900 border-b border-zinc-800 shadow-sm">
                    <tr>
                        <th class="w-12 px-3 py-2 border-r border-zinc-800 text-center">
                            <Icon icon="tabler:trash" class="text-zinc-700" />
                        </th>
                        <th 
                            v-for="col in columns" 
                            :key="col.name"
                            class="px-4 py-2 border-r border-zinc-800 min-w-[150px] group cursor-default"
                        >
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <Icon v-if="col.isPk" icon="tabler:key" class="text-amber-400 text-[10px]" />
                                    <span class="text-zinc-200">{{ col.name }}</span>
                                </div>
                                <span class="text-[9px] text-zinc-600 font-sans italic opacity-0 group-hover:opacity-100 transition-opacity">
                                    {{ col.type }}
                                </span>
                            </div>
                        </th>
                        <th class="w-full"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-900">
                    <!-- INSERT ROW -->
                    <tr v-if="isAddingRecord" class="bg-amber-400/5 group">
                        <td class="px-3 py-2 border-r border-zinc-800/50 text-center">
                            <Icon icon="tabler:plus" class="text-amber-400" />
                        </td>
                        <td v-for="col in columns" :key="col.name" class="px-4 py-2 border-r border-zinc-800/50">
                            <input
                                v-model="newRecord[col.name]"
                                class="w-full bg-transparent border-none p-0 text-amber-200 focus:ring-0 text-xs placeholder:text-amber-400/20"
                                :placeholder="col.default || 'NULL'"
                                :disabled="col.isPk && (col.default?.includes('nextval') || col.extra?.includes('auto_increment'))"
                            />
                        </td>
                        <td></td>
                    </tr>

                    <!-- DATA ROWS -->
                    <tr 
                        v-for="(row, idx) in tableData" 
                        :key="idx"
                        class="hover:bg-zinc-900/40 transition-colors group"
                        :class="{ 'bg-emerald-400/5': modifiedRows[idx] }"
                    >
                        <td class="px-3 py-2 border-r border-zinc-800/50 text-center">
                            <button @click="handleDelete(row)" class="opacity-0 group-hover:opacity-100 p-1 text-zinc-700 hover:text-rose-500 transition-all">
                                <Icon icon="tabler:trash" />
                            </button>
                        </td>
                        <td 
                            v-for="col in columns" 
                            :key="col.name"
                            class="px-4 py-2 border-r border-zinc-800/50 group/cell relative"
                            @dblclick="editingCell = { row: idx, col: col.name }"
                        >
                            <div v-if="editingCell?.row === idx && editingCell?.col === col.name">
                                <input
                                    :value="modifiedRows[idx] ? modifiedRows[idx][col.name] : row[col.name]"
                                    @input="(e: any) => handleCellEdit(idx, col.name, e.target.value)"
                                    @blur="editingCell = null"
                                    @keyup.enter="editingCell = null"
                                    class="w-full bg-zinc-900 border-none p-0 text-emerald-400 focus:ring-0 text-xs"
                                    autoFocus
                                />
                            </div>
                            <div v-else class="flex items-center justify-between">
                                <span 
                                    v-if="row[col.name] === null && !modifiedRows[idx]?.[col.name]" 
                                    class="text-zinc-700 italic text-[10px] uppercase tracking-widest"
                                >NULL</span>
                                <span v-else :class="[modifiedRows[idx]?.[col.name] !== undefined ? 'text-emerald-400' : 'text-zinc-400 group-hover:text-zinc-200']">
                                    {{ modifiedRows[idx] ? modifiedRows[idx][col.name] : row[col.name] }}
                                </span>
                                <button 
                                    @click="editingCell = { row: idx, col: col.name }"
                                    class="opacity-0 group-hover/cell:opacity-100 p-1 text-zinc-700 hover:text-emerald-400 transition-all"
                                >
                                    <Icon icon="tabler:edit-pencils" />
                                </button>
                            </div>
                        </td>
                        <td></td>
                    </tr>
                </tbody>
            </table>

            <!-- Structure View -->
            <div v-else class="p-8 max-w-5xl mx-auto">
                <div class="bg-zinc-900 border border-zinc-800 rounded overflow-hidden shadow-2xl">
                    <table class="w-full border-collapse text-left text-xs">
                        <thead>
                            <tr class="bg-zinc-800/50 border-b border-zinc-800">
                                <th class="px-6 py-3 text-[10px] font-black text-zinc-500 uppercase tracking-widest">Column</th>
                                <th class="px-6 py-3 text-[10px] font-black text-zinc-500 uppercase tracking-widest">Type</th>
                                <th class="px-6 py-3 text-[10px] font-black text-zinc-500 uppercase tracking-widest text-center">Nullable</th>
                                <th class="px-6 py-3 text-[10px] font-black text-zinc-500 uppercase tracking-widest">Default</th>
                                <th class="px-6 py-3 text-[10px] font-black text-zinc-500 uppercase tracking-widest text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-800/50">
                            <tr v-for="col in columns" :key="col.name" class="hover:bg-zinc-800/20 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <Icon v-if="col.isPk" icon="tabler:key" class="text-amber-400" />
                                        <span class="font-bold text-zinc-300">{{ col.name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 bg-zinc-950 border border-zinc-800 rounded text-[10px] font-mono text-emerald-400/80">
                                        {{ col.type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <Icon :icon="col.nullable ? 'tabler:check' : 'tabler:x'" :class="col.nullable ? 'text-emerald-500' : 'text-zinc-700'" class="mx-auto" />
                                </td>
                                <td class="px-6 py-4 text-[10px] font-mono text-zinc-500">
                                    {{ col.default || 'NULL' }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button class="p-1.5 text-zinc-600 hover:text-emerald-400 transition-colors"><Icon icon="tabler:edit" /></button>
                                        <button class="p-1.5 text-zinc-600 hover:text-rose-500 transition-colors"><Icon icon="tabler:trash" /></button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="h-8 flex items-center justify-between px-4 border-t border-zinc-800 bg-zinc-900/50 text-[10px] font-black text-zinc-600 uppercase tracking-widest shrink-0">
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span>Connected</span>
                </div>
                <div>MULTI-DBMS REST CLIENT</div>
            </div>
            <div class="flex items-center gap-4 font-mono">
                <span>LIMIT: {{ limit }}</span>
                <span>OFFSET: {{ offset }}</span>
            </div>
        </footer>
    </div>
</template>

<style>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #27272a;
    border-radius: 3px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #3f3f46;
}
</style>
