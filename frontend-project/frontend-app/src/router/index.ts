import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router';

const routes: RouteRecordRaw[] = [
    {
        path: '/',
        name: 'Dashboard',
        component: () => import('@/views/DashboardView.vue'),
        meta: { requiresAuth: true },
    },
    {
        path: '/connections',
        name: 'Connections',
        component: () => import('@/views/connections/ConnectionsListView.vue'),
        meta: { requiresAuth: true },
    },
    {
        path: '/connections/new',
        name: 'ConnectionCreate',
        component: () => import('@/views/connections/ConnectionFormView.vue'),
        meta: { requiresAuth: true },
    },
    {
        path: '/connections/:connectionId/edit',
        name: 'ConnectionEdit',
        component: () => import('@/views/connections/ConnectionFormView.vue'),
        props: true,
        meta: { requiresAuth: true },
    },
    // Database specific routes
    {
        path: '/c/:connectionId',
        component: () => import('@/views/database/DatabaseLayout.vue'),
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                name: 'DbOverview',
                component: () => import('@/views/database/OverviewView.vue'),
            },
            {
                path: 'sql',
                name: 'SqlEditor',
                component: () => import('@/views/database/SqlEditorView.vue'),
            },
            {
                path: 'tables',
                name: 'Tables',
                component: () => import('@/views/database/TablesListView.vue'),
            },
            {
                path: 'tables/new',
                name: 'TableCreate',
                component: () => import('@/views/database/TableFormView.vue'),
            },
            {
                path: 'tables/:tableName',
                name: 'TableDetail',
                component: () => import('@/views/database/TableDetailView.vue'),
                props: true,
            },
            {
                path: 'query-builder',
                name: 'QueryBuilder',
                component: () => import('@/views/database/QueryBuilderView.vue'),
            },
        ],
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
