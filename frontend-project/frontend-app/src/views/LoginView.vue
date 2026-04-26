<script setup lang="ts">
import { ref } from 'vue';
import { Icon } from '@iconify/vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const email = ref('');
const password = ref('');
const isLoading = ref(false);
const error = ref('');

const handleLogin = async () => {
    isLoading.value = true;
    error.value = '';

    try {
        // Mocking login for now
        // In a real app, this would call the API
        localStorage.setItem('easy_db_token', 'mock_token_123');
        router.push('/');
    } catch (e: any) {
        error.value = e.message || 'Login failed';
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-background p-4 relative overflow-hidden">
        <!-- Abstract Background Decoration -->
        <div
            class="absolute top-[-20%] left-[-10%] w-[500px] h-[500px] bg-emerald-400/5 rounded-full blur-[120px]"
        ></div>
        <div
            class="absolute bottom-[-20%] right-[-10%] w-[500px] h-[500px] bg-emerald-400/5 rounded-full blur-[120px]"
        ></div>

        <div class="w-full max-w-md z-10">
            <div class="text-center mb-8">
                <div
                    class="inline-flex items-center justify-center w-16 h-16 bg-zinc-900 border border-emerald-400/20 rounded-xl mb-4 shadow-2xl"
                >
                    <Icon icon="tabler:database-cog" class="text-emerald-400 text-3xl" />
                </div>
                <h1 class="text-2xl font-black text-on-surface tracking-tighter">EASY DB CONSOLE</h1>
                <p class="text-zinc-500 text-sm mt-1">Access your multi-DBMS infrastructure.</p>
            </div>

            <div class="bg-surface border border-border p-8 rounded-sm shadow-2xl">
                <form @submit.prevent="handleLogin" class="space-y-6">
                    <div
                        v-if="error"
                        class="bg-rose-400/10 border border-rose-400/20 text-rose-400 p-3 text-xs rounded-sm font-bold flex items-center gap-2"
                    >
                        <Icon icon="tabler:alert-circle" class="text-lg" />
                        {{ error }}
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-zinc-500">
                            Email Address
                        </label>
                        <div class="relative">
                            <Icon
                                icon="tabler:mail"
                                class="absolute left-3 top-1/2 -translate-y-1/2 text-zinc-600 text-lg"
                            />
                            <input
                                v-model="email"
                                type="email"
                                required
                                class="w-full bg-zinc-950 border border-border rounded-sm pl-10 pr-4 py-2.5 text-sm focus:border-emerald-400 focus:ring-1 focus:ring-emerald-400 outline-none transition-all placeholder:text-zinc-700"
                                placeholder="admin@example.com"
                            />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-zinc-500">
                            Password
                        </label>
                        <div class="relative">
                            <Icon
                                icon="tabler:lock"
                                class="absolute left-3 top-1/2 -translate-y-1/2 text-zinc-600 text-lg"
                            />
                            <input
                                v-model="password"
                                type="password"
                                required
                                class="w-full bg-zinc-950 border border-border rounded-sm pl-10 pr-4 py-2.5 text-sm focus:border-emerald-400 focus:ring-1 focus:ring-emerald-400 outline-none transition-all placeholder:text-zinc-700"
                                placeholder="••••••••"
                            />
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer group">
                            <input
                                type="checkbox"
                                class="w-4 h-4 rounded-sm bg-zinc-950 border-border text-emerald-400 focus:ring-emerald-400"
                            />
                            <span
                                class="text-[10px] font-bold uppercase tracking-widest text-zinc-500 group-hover:text-zinc-300 transition-colors"
                            >
                                Remember me
                            </span>
                        </label>
                        <a
                            href="#"
                            class="text-[10px] font-bold uppercase tracking-widest text-emerald-400 hover:text-emerald-300 transition-colors"
                        >
                            Forgot Password?
                        </a>
                    </div>

                    <button
                        type="submit"
                        :disabled="isLoading"
                        class="w-full bg-primary-container text-zinc-950 font-black py-3 rounded-sm text-xs tracking-[0.2em] uppercase hover:opacity-90 active:scale-[0.98] transition-all flex items-center justify-center gap-2"
                    >
                        <Icon v-if="isLoading" icon="tabler:loader-2" class="animate-spin text-xl" />
                        <span v-else>SIGN IN</span>
                    </button>
                </form>
            </div>

            <p class="text-center mt-8 text-[10px] font-bold uppercase tracking-[0.2em] text-zinc-600">
                Don't have an account?
                <a href="#" class="text-emerald-400 hover:text-emerald-300 transition-colors">Contact Admin</a>
            </p>
        </div>
    </div>
</template>
