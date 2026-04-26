<script setup lang="ts">
import { Icon } from '@iconify/vue';

const metrics = [
    { name: 'Compute', value: '1,240', unit: 'CU-hrs', change: '+12.4%', icon: 'tabler:cpu', progress: '65%' },
    { name: 'Storage', value: '42.8', unit: 'GB', change: 'Stable', icon: 'tabler:database', progress: '40%' },
    {
        name: 'Network',
        value: '892',
        unit: 'GB',
        change: '+28.1%',
        icon: 'tabler:arrows-left-right',
        progress: '82%',
        isError: true,
    },
];

const projects = [
    { name: 'billing-microservice-db', region: 'us-east-1', status: 'Active', created: 'Oct 24, 2023' },
    { name: 'vector-search-engine', region: 'eu-central-1', status: 'Active', created: 'Nov 12, 2023' },
    { name: 'temp-staging-cache', region: 'us-west-2', status: 'Paused', created: 'Jan 05, 2024' },
    { name: 'auth-production-db', region: 'us-east-1', status: 'Active', created: 'May 20, 2023' },
];
</script>

<template>
    <div class="flex-1 bg-surface-container-lowest overflow-y-auto p-8">
        <div class="max-w-7xl mx-auto space-y-8">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-on-surface tracking-tight">Overview</h1>
                    <p class="text-on-secondary-container text-sm">
                        Manage and monitor your infrastructure performance in real-time.
                    </p>
                </div>
                <div class="flex items-center gap-2 bg-zinc-900 p-1 rounded-lg border border-zinc-800">
                    <button class="px-4 py-1.5 bg-zinc-800 text-on-surface text-xs font-medium rounded-md">
                        24 Hours
                    </button>
                    <button class="px-4 py-1.5 text-zinc-500 text-xs font-medium hover:text-zinc-300 transition-colors">
                        7 Days
                    </button>
                    <button class="px-4 py-1.5 text-zinc-500 text-xs font-medium hover:text-zinc-300 transition-colors">
                        30 Days
                    </button>
                </div>
            </div>

            <!-- Bento Grid Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div
                    v-for="m in metrics"
                    :key="m.name"
                    class="bg-surface border border-border p-4 group hover:border-emerald-400/50 transition-colors"
                >
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center gap-2">
                            <Icon :icon="m.icon" class="text-emerald-400 text-xl" />
                            <span class="text-[11px] font-bold uppercase tracking-wider text-on-secondary-container">
                                {{ m.name }}
                            </span>
                        </div>
                        <span :class="[m.isError ? 'text-rose-400' : 'text-emerald-400']" class="text-xs font-bold">
                            {{ m.change }}
                        </span>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-bold text-on-surface">{{ m.value }}</span>
                        <span class="text-sm text-zinc-500">{{ m.unit }}</span>
                    </div>
                    <div class="mt-4 h-1 w-full bg-zinc-800 overflow-hidden rounded-full">
                        <div
                            class="h-full bg-emerald-400 transition-all duration-500"
                            :style="{ width: m.progress }"
                            :class="{ 'shadow-[0_0_8px_rgba(0,229,153,0.4)]': !m.isError }"
                        ></div>
                    </div>
                </div>
            </div>

            <!-- Projects Table Section -->
            <div class="space-y-4">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <h2 class="text-lg font-bold text-on-surface self-start">Active Connections</h2>
                    <div class="relative w-full md:w-64">
                        <Icon
                            icon="tabler:search"
                            class="absolute left-3 top-1/2 -translate-y-1/2 text-zinc-500 text-lg"
                        />
                        <input
                            class="w-full bg-surface border border-border rounded-sm pl-10 pr-4 py-2 text-sm focus:outline-none focus:border-emerald-400 transition-colors placeholder:text-zinc-600"
                            placeholder="Search connections..."
                            type="text"
                        />
                    </div>
                </div>

                <div class="bg-surface border border-border overflow-hidden rounded-sm">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-zinc-900/50 border-b border-border">
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-widest text-zinc-500">
                                    Connection Name
                                </th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-widest text-zinc-500">
                                    Region
                                </th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-widest text-zinc-500">
                                    Status
                                </th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-widest text-zinc-500">
                                    Created
                                </th>
                                <th
                                    class="px-6 py-4 text-[10px] font-bold uppercase tracking-widest text-zinc-500 text-right"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="p in projects" :key="p.name" class="hover:bg-zinc-900 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-2 h-2 rounded-full"
                                            :class="[
                                                p.status === 'Active'
                                                    ? 'bg-emerald-400 shadow-[0_0_6px_rgba(0,229,153,0.6)]'
                                                    : 'bg-zinc-600',
                                            ]"
                                        ></div>
                                        <span class="font-medium text-on-surface">{{ p.name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-zinc-400 font-mono text-xs">{{ p.region }}</td>
                                <td class="px-6 py-4">
                                    <span
                                        class="px-2 py-0.5 text-[10px] font-bold uppercase rounded border"
                                        :class="[
                                            p.status === 'Active'
                                                ? 'bg-emerald-400/10 text-emerald-400 border-emerald-400/20'
                                                : 'bg-zinc-800 text-zinc-500 border-zinc-700',
                                        ]"
                                    >
                                        {{ p.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-zinc-400 text-sm">{{ p.created }}</td>
                                <td class="px-6 py-4 text-right">
                                    <button
                                        class="p-1 hover:bg-zinc-800 rounded transition-colors text-zinc-500 hover:text-on-surface"
                                    >
                                        <Icon icon="tabler:dots-vertical" class="text-xl" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>
