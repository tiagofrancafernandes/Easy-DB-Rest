<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useConnections } from '@/composables/useConnections';
import { useForm } from '@/composables/useForm';
import { Icon } from '@iconify/vue';

const props = defineProps<{
    connectionId?: string;
}>();

const { fetchConnection, createConnection, updateConnection, isLoading } = useConnections();
const router = useRouter();

const schema = {
    name: { required: true, label: 'Name' },
    driver: { required: true, label: 'Driver' },
    host: { required: true, label: 'Host' },
    port: { required: true, label: 'Port' },
    database: { required: true, label: 'Database' },
    username: { required: true, label: 'Username' },
};

const { values, errors, validate, isSubmitting } = useForm<any>(schema);

onMounted(async () => {
    if (props.connectionId) {
        const data = await fetchConnection(props.connectionId);
        values.value = { ...data, password: '' };
    } else {
        values.value = {
            driver: 'pgsql',
            host: 'localhost',
            port: '5432',
            database: '',
            username: 'postgres',
        };
    }
});

const handleSubmit = async () => {
    if (!validate()) return;

    isSubmitting.value = true;
    try {
        if (props.connectionId) {
            await updateConnection(props.connectionId, values.value);
        } else {
            await createConnection(values.value);
        }
        router.push('/connections');
    } catch (e) {
        console.error(e);
    } finally {
        isSubmitting.value = false;
    }
};
</script>

<template>
    <div class="flex-1 bg-surface-container-lowest overflow-y-auto p-8">
        <div class="max-w-2xl mx-auto">
            <div class="flex items-center gap-4 mb-8">
                <button
                    @click="router.back()"
                    class="p-2 hover:bg-zinc-900 rounded-full text-zinc-500 hover:text-on-surface transition-colors"
                >
                    <Icon icon="tabler:arrow-left" class="text-xl" />
                </button>
                <h1 class="text-2xl font-bold text-on-surface tracking-tight">
                    {{ connectionId ? 'Edit Connection' : 'New Connection' }}
                </h1>
            </div>

            <div class="bg-surface border border-border p-8 rounded-sm shadow-2xl">
                <form @submit.prevent="handleSubmit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2 space-y-2">
                            <label class="block text-[10px] font-bold uppercase tracking-widest text-zinc-500">
                                Connection Name
                            </label>
                            <input
                                v-model="values.name"
                                type="text"
                                class="w-full bg-zinc-950 border border-border rounded-sm px-4 py-2.5 text-sm focus:border-emerald-400 outline-none"
                                placeholder="My Production DB"
                            />
                            <p v-if="errors.name" class="text-rose-400 text-[10px] uppercase font-bold">
                                {{ errors.name }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-[10px] font-bold uppercase tracking-widest text-zinc-500">
                                Driver
                            </label>
                            <select
                                v-model="values.driver"
                                class="w-full bg-zinc-950 border border-border rounded-sm px-4 py-2.5 text-sm focus:border-emerald-400 outline-none appearance-none"
                            >
                                <option value="pgsql">PostgreSQL</option>
                                <option value="mysql">MySQL</option>
                                <option value="sqlite">SQLite</option>
                                <option value="sqlsrv">SQL Server</option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-[10px] font-bold uppercase tracking-widest text-zinc-500">
                                Host
                            </label>
                            <input
                                v-model="values.host"
                                type="text"
                                class="w-full bg-zinc-950 border border-border rounded-sm px-4 py-2.5 text-sm focus:border-emerald-400 outline-none"
                            />
                        </div>

                        <div class="space-y-2">
                            <label class="block text-[10px] font-bold uppercase tracking-widest text-zinc-500">
                                Port
                            </label>
                            <input
                                v-model="values.port"
                                type="text"
                                class="w-full bg-zinc-950 border border-border rounded-sm px-4 py-2.5 text-sm focus:border-emerald-400 outline-none"
                            />
                        </div>

                        <div class="space-y-2">
                            <label class="block text-[10px] font-bold uppercase tracking-widest text-zinc-500">
                                Database
                            </label>
                            <input
                                v-model="values.database"
                                type="text"
                                class="w-full bg-zinc-950 border border-border rounded-sm px-4 py-2.5 text-sm focus:border-emerald-400 outline-none"
                            />
                        </div>

                        <div class="space-y-2">
                            <label class="block text-[10px] font-bold uppercase tracking-widest text-zinc-500">
                                Username
                            </label>
                            <input
                                v-model="values.username"
                                type="text"
                                class="w-full bg-zinc-950 border border-border rounded-sm px-4 py-2.5 text-sm focus:border-emerald-400 outline-none"
                            />
                        </div>

                        <div class="space-y-2">
                            <label class="block text-[10px] font-bold uppercase tracking-widest text-zinc-500">
                                Password
                            </label>
                            <input
                                v-model="values.password"
                                type="password"
                                class="w-full bg-zinc-950 border border-border rounded-sm px-4 py-2.5 text-sm focus:border-emerald-400 outline-none"
                                placeholder="••••••••"
                            />
                        </div>
                    </div>

                    <div class="pt-6 border-t border-border flex justify-end gap-3">
                        <button
                            type="button"
                            @click="router.back()"
                            class="px-6 py-2.5 text-xs font-bold uppercase tracking-widest text-zinc-500 hover:text-on-surface transition-colors"
                        >
                            CANCEL
                        </button>
                        <button
                            type="submit"
                            :disabled="isSubmitting"
                            class="bg-primary-container text-zinc-950 px-8 py-2.5 rounded-sm font-black text-xs tracking-widest hover:opacity-90 active:scale-[0.98] transition-all flex items-center gap-2"
                        >
                            <Icon v-if="isSubmitting" icon="tabler:loader-2" class="animate-spin text-lg" />
                            {{ connectionId ? 'SAVE CHANGES' : 'CREATE CONNECTION' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
