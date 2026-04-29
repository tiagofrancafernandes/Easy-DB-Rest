<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Icon } from '@iconify/vue';
import { useRouter } from 'vue-router';
import { useAuth } from '@/composables/useAuth';
import { useConnections } from '@/composables/useConnections';

const appName = import.meta.env.VITE_APP_NAME || 'Console';
const isMenuOpen = ref(false);
const isSearchOpen = ref(false);
const searchQuery = ref('');
const router = useRouter();
const { logout } = useAuth();
const { connections, fetchConnections } = useConnections();

onMounted(fetchConnections);

const filteredConnections = computed(() => {
    if (!searchQuery.value) return connections.value;
    const query = searchQuery.value.toLowerCase();
    return connections.value.filter(
        (c) => c.name.toLowerCase().includes(query) || c.driver.toLowerCase().includes(query)
    );
});

const toggleMenu = () => {
    isMenuOpen.value = !isMenuOpen.value;
};

const closeAllMenus = (e: MouseEvent) => {
    const target = e.target as HTMLElement;
    if (isMenuOpen.value && !target.closest('.user-menu-container')) {
        isMenuOpen.value = false;
    }
    if (isSearchOpen.value && !target.closest('.search-container')) {
        isSearchOpen.value = false;
    }
};

const selectConnection = (id: string) => {
    router.push(`/c/${id}`);
    isSearchOpen.value = false;
    searchQuery.value = '';
};

const handleLogout = () => {
    logout();
    router.push('/login');
};

onMounted(() => window.addEventListener('click', closeAllMenus));
onUnmounted(() => window.removeEventListener('click', closeAllMenus));
</script>

<template>
    <header
        class="bg-zinc-950 flex justify-between items-center h-14 px-4 w-full z-50 border-b border-zinc-800 fixed top-0"
    >
        <div class="flex items-center gap-6">
            <span class="text-lg font-bold text-white tracking-tight">{{ appName }}</span>
            <div class="hidden md:flex gap-4">
                <a
                    class="text-zinc-400 hover:text-emerald-400 hover:bg-zinc-900 transition-colors font-sans text-sm tracking-tight px-2 py-1 rounded"
                    href="#"
                >
                    Docs
                </a>
                <a
                    class="text-zinc-400 hover:text-emerald-400 hover:bg-zinc-900 transition-colors font-sans text-sm tracking-tight px-2 py-1 rounded"
                    href="#"
                >
                    Feedback
                </a>
                <a
                    class="text-zinc-400 hover:text-emerald-400 hover:bg-zinc-900 transition-colors font-sans text-sm tracking-tight px-2 py-1 rounded"
                    href="#"
                >
                    Support
                </a>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <!-- Connection Search / Selector -->
            <div class="relative search-container">
                <div
                    class="relative flex items-center bg-zinc-900 border border-zinc-800 rounded px-3 py-1.5 w-72 transition-all"
                    :class="[isSearchOpen ? 'border-emerald-400/50 ring-1 ring-emerald-400/20' : '']"
                >
                    <Icon icon="tabler:search" class="text-zinc-500 text-sm mr-2" />
                    <input
                        v-model="searchQuery"
                        @focus="isSearchOpen = true"
                        class="bg-transparent border-none focus:ring-0 p-0 text-xs text-zinc-300 w-full"
                        placeholder="Search connections..."
                        type="text"
                    />
                    <span class="text-[10px] text-zinc-600 bg-zinc-950 px-1 border border-zinc-800 rounded">⌘K</span>
                </div>

                <!-- Dropdown -->
                <transition
                    enter-active-class="transition duration-100 ease-out"
                    enter-from-class="transform scale-95 opacity-0"
                    enter-to-class="transform scale-100 opacity-100"
                    leave-active-class="transition duration-75 ease-in"
                    leave-from-class="transform scale-100 opacity-100"
                    leave-to-class="transform scale-95 opacity-0"
                >
                    <div
                        v-if="isSearchOpen"
                        class="absolute left-0 mt-2 w-full origin-top bg-zinc-950 border border-zinc-800 rounded-sm shadow-2xl z-[60] overflow-hidden"
                    >
                        <div class="p-2 border-b border-zinc-900">
                            <button
                                @click="
                                    router.push('/connections/new');
                                    isSearchOpen = false;
                                "
                                class="w-full flex items-center gap-3 px-3 py-2 text-xs font-bold text-emerald-400 hover:bg-zinc-900 rounded-sm transition-colors"
                            >
                                <Icon icon="tabler:plus" class="text-lg" />
                                ADD NEW CONNECTION
                            </button>
                        </div>

                        <div class="max-h-64 overflow-y-auto py-1">
                            <div v-if="!filteredConnections.length" class="px-4 py-4 text-center">
                                <p class="text-[10px] uppercase font-black text-zinc-600 tracking-widest">
                                    No connections found
                                </p>
                            </div>
                            <button
                                v-for="conn in filteredConnections"
                                :key="conn.id"
                                @click="selectConnection(conn.id)"
                                class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-zinc-900 group transition-all"
                            >
                                <div class="flex items-center gap-3">
                                    <Icon
                                        :icon="
                                            conn.driver === 'postgres' ? 'tabler:brand-postgresql' : 'tabler:database'
                                        "
                                        class="text-lg text-zinc-500 group-hover:text-emerald-400"
                                    />
                                    <div>
                                        <p class="text-xs font-bold text-zinc-300 group-hover:text-on-surface">
                                            {{ conn.name }}
                                        </p>
                                        <p class="text-[9px] text-zinc-600 uppercase font-black tracking-widest">
                                            {{ conn.driver }}
                                        </p>
                                    </div>
                                </div>
                                <Icon
                                    icon="tabler:arrow-right"
                                    class="text-zinc-800 group-hover:text-emerald-400 transition-all opacity-0 group-hover:opacity-100"
                                />
                            </button>
                        </div>
                    </div>
                </transition>
            </div>

            <button class="text-zinc-400 hover:text-emerald-400 p-1.5 rounded transition-all">
                <Icon icon="tabler:bell" class="text-xl" />
            </button>
            <button class="text-zinc-400 hover:text-emerald-400 p-1.5 rounded transition-all">
                <Icon icon="tabler:settings" class="text-xl" />
            </button>

            <!-- User Menu -->
            <div class="relative user-menu-container">
                <button
                    @click.stop="toggleMenu"
                    class="w-8 h-8 rounded-full bg-emerald-400 flex items-center justify-center text-zinc-950 font-bold text-xs overflow-hidden border-2 border-zinc-900 hover:border-emerald-400 transition-all active:scale-90"
                >
                    <img
                        class="w-full h-full object-cover"
                        src="https://ui-avatars.com/api/?name=User&background=6dffba&color=003822"
                        alt="Profile"
                    />
                </button>

                <transition
                    enter-active-class="transition duration-100 ease-out"
                    enter-from-class="transform scale-95 opacity-0"
                    enter-to-class="transform scale-100 opacity-100"
                    leave-active-class="transition duration-75 ease-in"
                    leave-from-class="transform scale-100 opacity-100"
                    leave-to-class="transform scale-95 opacity-0"
                >
                    <div
                        v-if="isMenuOpen"
                        class="absolute right-0 mt-2 w-56 origin-top-right bg-zinc-950 border border-zinc-800 rounded-sm shadow-2xl z-[60] overflow-hidden animate-in fade-in zoom-in duration-150"
                    >
                        <div class="px-4 py-3 border-b border-zinc-900 bg-zinc-900/30">
                            <p class="text-[10px] font-black text-zinc-500 uppercase tracking-widest">Signed in as</p>
                            <p class="text-xs font-bold text-on-surface truncate">admin@mail.com</p>
                        </div>
                        <div class="py-1">
                            <button
                                @click="isMenuOpen = false"
                                class="w-full flex items-center gap-3 px-4 py-2 text-xs text-zinc-400 hover:bg-zinc-900 hover:text-emerald-400 transition-colors"
                            >
                                <Icon icon="tabler:user-circle" class="text-lg" />
                                Your Profile
                            </button>
                            <button
                                @click="isMenuOpen = false"
                                class="w-full flex items-center gap-3 px-4 py-2 text-xs text-zinc-400 hover:bg-zinc-900 hover:text-emerald-400 transition-colors"
                            >
                                <Icon icon="tabler:settings" class="text-lg" />
                                Account Settings
                            </button>
                        </div>
                        <div class="py-1 border-t border-zinc-900">
                            <button
                                @click="handleLogout"
                                class="w-full flex items-center gap-3 px-4 py-2 text-xs text-rose-400 hover:bg-rose-500/10 transition-colors font-bold"
                            >
                                <Icon icon="tabler:logout" class="text-lg" />
                                Sign Out
                            </button>
                        </div>
                    </div>
                </transition>
            </div>
        </div>
    </header>
</template>
