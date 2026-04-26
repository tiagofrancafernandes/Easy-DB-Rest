<script setup lang="ts">
import { RouterView } from 'vue-router';
import MainLayout from '@/components/layout/MainLayout.vue';
import { useRoute } from 'vue-router';
import { computed } from 'vue';

const route = useRoute();
const isAuthPage = computed(() => route.name === 'Login');
</script>

<template>
    <template v-if="isAuthPage">
        <RouterView />
    </template>
    <template v-else>
        <MainLayout>
            <RouterView />
        </MainLayout>
    </template>
</template>
