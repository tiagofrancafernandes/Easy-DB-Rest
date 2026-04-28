<script setup lang="ts">
import { onMounted } from 'vue';
import { Icon } from '@iconify/vue';
import { useConnections } from '@/composables/useConnections';
import { useRouter } from 'vue-router';
import { formatDate } from '@/utils/formatters';

const { connections, isLoading, fetchConnections, deleteConnection } = useConnections();
const router = useRouter();

onMounted(fetchConnections);

const handleDelete = async (id: string) => {
    if (confirm('Are you sure you want to delete this connection?')) {
        await deleteConnection(id);
    }
};
</script>

<template>
    <div class="flex-1 bg-surface-container-lowest overflow-y-auto p-8">
        <div class="max-w-7xl mx-auto space-y-8">
            <div class="flex justify-between items-end">
                <div>
                    <h1 class="text-3xl font-bold text-on-surface tracking-tight">Connections</h1>
                    <p class="text-on-surface-variant text-sm mt-1">
                        Manage your database connections across multiple providers.
                    </p>
                </div>
                <button
                    @click="router.push('/connections/new')"
                    class="bg-primary-container text-zinc-950 px-6 py-2.5 rounded-sm font-bold text-xs tracking-widest hover:opacity-90 transition-all flex items-center gap-2"
                >
                    <Icon icon="tabler:plus" class="text-lg" />
                    CREATE CONNECTION
                </button>
            </div>

            <div v-if="isLoading && !connections.length" class="flex justify-center py-20">
                <Icon icon="tabler:loader-2" class="text-4xl text-emerald-400 animate-spin" />
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="conn in connections"
                    :key="conn.id"
                    class="bg-surface border border-border p-6 rounded-sm group hover:border-emerald-400/50 transition-all relative overflow-hidden"
                >
                    <div
                        class="absolute top-0 right-0 p-4 opacity-0 group-hover:opacity-100 transition-opacity flex gap-2"
                    >
                        <button
                            @click="router.push(`/connections/${conn.id}/edit`)"
                            class="p-1.5 bg-zinc-900 border border-zinc-800 rounded hover:text-emerald-400 transition-colors"
                        >
                            <Icon icon="tabler:edit" />
                        </button>
                        <button
                            @click="handleDelete(conn.id)"
                            class="p-1.5 bg-zinc-900 border border-zinc-800 rounded hover:text-rose-400 transition-colors"
                        >
                            <Icon icon="tabler:trash" />
                        </button>
                    </div>

                    <div class="flex items-start gap-4 mb-6">
                        <div
                            class="w-12 h-12 bg-zinc-900 rounded-lg border border-zinc-800 flex items-center justify-center shrink-0"
                        >
                            <Icon
                                :icon="conn.driver === 'postgres' ? 'tabler:brand-postgresql' : 'tabler:database'"
                                class="text-2xl"
                                :class="[conn.driver === 'postgres' ? 'text-blue-400' : 'text-emerald-400']"
                            />
                        </div>
                        <div class="min-w-0">
                            <h3 class="font-bold text-on-surface truncate pr-8">{{ conn.name }}</h3>
                            <p class="text-[10px] text-zinc-500 uppercase font-bold tracking-widest">
                                {{ conn.driver }}
                            </p>
                        </div>
                    </div>

                    <div class="space-y-2 mb-6">
                        <div class="flex justify-between text-xs">
                            <span class="text-zinc-500">Host</span>
                            <span class="text-on-surface font-mono">{{ conn.host }}:{{ conn.port }}</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-zinc-500">Database</span>
                            <span class="text-on-surface font-mono truncate ml-4">{{ conn.database }}</span>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-border flex justify-between items-center">
                        <span class="text-[10px] text-zinc-600 font-medium">
                            Created {{ formatDate(conn.created_at) }}
                        </span>
                        <button
                            @click="router.push(`/c/${conn.id}`)"
                            class="text-[10px] font-black text-emerald-400 uppercase tracking-widest hover:text-white transition-colors flex items-center gap-1 group/btn"
                        >
                            CONNECT
                            <Icon
                                icon="tabler:arrow-right"
                                class="group-hover/btn:translate-x-1 transition-transform"
                            />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
