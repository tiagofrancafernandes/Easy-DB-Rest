<script setup lang="ts">
import { computed, onMounted, provide, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useConnections } from '@/composables/useConnections';
import { useDatabase } from '@/composables/useDatabase';
import { useToast } from '@/composables/useToast';
import { Icon } from '@iconify/vue';

const route = useRoute();
const router = useRouter();
const connectionId = computed(() => route.params.connectionId as string);
const toast = useToast();

const { currentConnection, fetchConnection } = useConnections();

// We need to keep these reactive
const dbHelper = computed(() => useDatabase(connectionId.value));

const tables = computed(() => dbHelper.value.tables.value);
const isLoading = computed(() => dbHelper.value.isLoading.value);
const currentDatabase = computed(() => dbHelper.value.currentDatabase.value);
const currentSchema = computed(() => dbHelper.value.currentSchema.value);

provide('connectionId', connectionId);

const databases = ref<string[]>([]);
const schemas = ref<string[]>([]);
const searchQuery = ref('');

const loadMetadata = async () => {
    try {
        databases.value = await dbHelper.value.fetchDatabases();
        if (databases.value.length && !currentDatabase.value) {
            dbHelper.value.setDatabase(databases.value[0]);
        }
        schemas.value = await dbHelper.value.fetchSchemas();
        await dbHelper.value.fetchTables();
    } catch (e: any) {
        toast.error(`Failed to load database metadata: ${e.message}`);
    }
};

const init = async () => {
    try {
        await fetchConnection(connectionId.value);
        await loadMetadata();
    } catch (e) {
        router.push('/connections');
    }
};

onMounted(init);

// Watch for connection changes
watch(connectionId, init);

watch([currentDatabase, currentSchema], async () => {
    await dbHelper.value.fetchTables();
    schemas.value = await dbHelper.value.fetchSchemas();
});

const filteredTables = computed(() => {
    if (!searchQuery.value) return tables.value;
    return tables.value.filter(t => t.name.toLowerCase().includes(searchQuery.value.toLowerCase()));
});

</script>

<template>
    <div class="h-screen flex bg-zinc-950 text-on-surface overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-72 flex flex-col border-r border-zinc-800 bg-zinc-950 shrink-0">
            <!-- Connection Info -->
            <div class="p-6 border-b border-zinc-800 bg-zinc-900/20">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-zinc-900 border border-zinc-800 flex items-center justify-center group-hover:border-emerald-500/50 transition-all">
                            <Icon icon="tabler:database" class="text-xl text-emerald-400" />
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-white tracking-tight">{{ currentConnection?.name }}</h2>
                            <p class="text-[10px] text-zinc-500 font-mono uppercase tracking-widest">{{ currentConnection?.driver }}</p>
                        </div>
                    </div>
                </div>

                <!-- Database/Schema Selectors -->
                <div class="space-y-4">
                    <div class="space-y-1.5 group">
                        <label class="text-[10px] font-black text-zinc-500 uppercase tracking-widest px-1">Database</label>
                        <div class="relative">
                            <select
                                :value="currentDatabase"
                                @change="(e: any) => dbHelper.value.setDatabase(e.target.value)"
                                class="w-full appearance-none bg-zinc-900 border border-zinc-800 rounded px-3 py-2 text-xs text-on-surface hover:border-zinc-700 focus:ring-1 focus:ring-emerald-400 outline-none transition-all cursor-pointer"
                            >
                                <option v-for="db in databases" :key="db" :value="db">{{ db }}</option>
                            </select>
                            <Icon icon="tabler:chevron-down" class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-500 pointer-events-none group-hover:text-zinc-300" />
                        </div>
                    </div>

                    <div v-if="schemas.length" class="space-y-1.5 group animate-in fade-in slide-in-from-top-2 duration-300">
                        <label class="text-[10px] font-black text-zinc-500 uppercase tracking-widest px-1">Schema</label>
                        <div class="relative">
                            <select
                                :value="currentSchema"
                                @change="(e: any) => dbHelper.value.setSchema(e.target.value)"
                                class="w-full appearance-none bg-zinc-900 border border-zinc-800 rounded px-3 py-2 text-xs text-on-surface hover:border-zinc-700 focus:ring-1 focus:ring-emerald-400 outline-none transition-all cursor-pointer"
                            >
                                <option v-for="s in schemas" :key="s" :value="s">{{ s }}</option>
                            </select>
                            <Icon icon="tabler:chevron-down" class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-500 pointer-events-none group-hover:text-zinc-300" />
                        </div>
                    </div>
                </div>

                <!-- Search \u0026 Add Table -->
                <div class="flex gap-2 mt-6">
                    <div class="relative flex-1 group">
                        <Icon icon="tabler:search" class="absolute left-2.5 top-1/2 -translate-y-1/2 text-zinc-500 text-sm group-focus-within:text-emerald-400" />
                        <input
                            v-model="searchQuery"
                            class="w-full bg-zinc-900 border border-zinc-800 rounded pl-8 pr-2 py-1.5 text-xs text-on-surface focus:outline-none focus:border-emerald-500/50 transition-colors placeholder:text-zinc-600"
                            placeholder="Filter tables..."
                            type="text"
                        />
                    </div>
                    <button
                        @click="router.push(`/c/${connectionId}/tables/new`)"
                        class="w-9 h-9 flex items-center justify-center bg-zinc-900 border border-zinc-800 rounded hover:bg-zinc-800 text-zinc-400 hover:text-emerald-400 transition-all active:scale-90"
                        title="New Table"
                    >
                        <Icon icon="tabler:plus" class="text-lg" />
                    </button>
                    <button
                        @click="dbHelper.fetchTables()"
                        class="w-9 h-9 flex items-center justify-center bg-zinc-900 border border-zinc-800 rounded hover:bg-zinc-800 text-zinc-400 hover:text-emerald-400 transition-all active:scale-90"
                        :class="{ 'animate-spin': isLoading }"
                        title="Refresh Tables"
                    >
                        <Icon icon="tabler:refresh" class="text-lg" />
                    </button>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto custom-scrollbar px-2 py-4">
                <div class="space-y-0.5">
                    <router-link
                        v-for="table in filteredTables"
                        :key="table.name"
                        :to="`/c/${connectionId}/tables/${table.name}`"
                        class="flex items-center gap-3 px-3 py-2 rounded text-xs transition-all group"
                        :class="[route.params.tableName === table.name ? 'bg-emerald-500/10 text-emerald-400 font-bold border-l-2 border-emerald-500' : 'text-zinc-500 hover:bg-zinc-900 hover:text-zinc-300']"
                    >
                        <Icon icon="tabler:table" class="text-base opacity-40 group-hover:opacity-100" />
                        <span class="truncate flex-1">{{ table.name }}</span>
                        <span v-if="table.rows" class="text-[9px] font-mono opacity-30">{{ table.rows }}</span>
                    </router-link>
                </div>
            </div>

            <!-- Team / Settings Footer -->
            <div class="p-4 border-t border-zinc-800 bg-zinc-950">
                <button 
                    @click="router.push(`/c/${connectionId}/settings`)"
                    class="w-full flex items-center gap-3 px-3 py-2 rounded text-xs text-zinc-500 hover:bg-zinc-900 hover:text-zinc-300 transition-all"
                >
                    <Icon icon="tabler:settings" class="text-base" />
                    <span>Connection Settings</span>
                </button>
            </div>
        </aside>

        <!-- Main View -->
        <main class="flex-1 flex flex-col relative overflow-hidden bg-zinc-950">
            <router-view v-slot="{ Component }">
                <transition
                    name="fade"
                    mode="out-in"
                >
                    <component :is="Component" />
                </transition>
            </router-view>
        </main>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #27272a;
    border-radius: 2px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #3f3f46;
}
</style>
