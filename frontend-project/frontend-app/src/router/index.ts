import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router';

const routes: RouteRecordRaw[] = [
    {
        path: '/',
        name: 'Dashboard',
        component: () => import('@/views/DashboardView.vue'),
        meta: { requiresAuth: true },
    },
    {
        path: '/sql',
        name: 'SqlEditor',
        component: () => import('@/views/SqlEditorView.vue'),
        meta: { requiresAuth: true },
    },
    {
        path: '/tables',
        name: 'Tables',
        component: () => import('@/views/TablesView.vue'),
        meta: { requiresAuth: true },
    },
    {
        path: '/login',
        name: 'Login',
        component: () => import('@/views/LoginView.vue'),
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, _from, next) => {
    const token = localStorage.getItem('easy_db_token');

    if (to.meta.requiresAuth && !token) {
        next('/login');
        return;
    }

    next();
});

export default router;
