import { createRouter, createWebHistory } from 'vue-router';
import Home from '@/views/Home.vue';
import Layout from '@/components/Layout.vue';

const routes = [
    {
        path: '/',
        component: Layout,
        children: [
            {
                path: '',
                name: 'home',
                component: Home,
                meta: { title: 'Home' },
            },
            {
                path: 'campaigns',
                name: 'campaigns',
                component: () => import('@/views/Campaigns.vue'),
                meta: { title: 'Campaigns' },
            },
            {
                path: 'campaigns/:id',
                name: 'campaign-detail',
                component: () => import('@/views/CampaignDetail.vue'),
                meta: { title: 'Campaign Detail' },
            },
            {
                path: 'campaigns/create',
                name: 'campaign-create',
                component: () => import('@/views/CampaignCreate.vue'),
                meta: { title: 'Buat Campaign', requiresAuth: true },
            },
            {
                path: 'my-campaigns',
                name: 'my-campaigns',
                component: () => import('@/views/MyCampaigns.vue'),
                meta: { title: 'Kampanye Saya', requiresAuth: true },
            },
            {
                path: 'my-backings',
                name: 'my-backings',
                component: () => import('@/views/MyBackings.vue'),
                meta: { title: 'Donasi Saya', requiresAuth: true },
            },
            {
                path: 'transactions',
                name: 'transactions',
                component: () => import('@/views/MyTransactions.vue'),
                meta: { title: 'Transaksi', requiresAuth: true },
            },
            {
                path: 'login',
                name: 'login',
                component: () => import('@/views/Login.vue'),
                meta: { title: 'Login' },
            },
            {
                path: 'register',
                name: 'register',
                component: () => import('@/views/Register.vue'),
                meta: { title: 'Register' },
            },
            {
                path: 'dashboard',
                name: 'dashboard',
                component: () => import('@/views/Dashboard.vue'),
                meta: { title: 'Dashboard', requiresAuth: true },
            },
            {
                path: 'categories',
                name: 'categories',
                component: () => import('@/views/Categories.vue'),
                meta: { title: 'Categories' },
            },
            {
                path: ':pathMatch(.*)*',
                name: 'not-found',
                component: () => import('@/views/NotFound.vue'),
                meta: { title: 'Not Found' },
            },
        ],
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Navigation guard for auth
router.beforeEach((to, from, next) => {
    document.title = to.meta.title ? `${to.meta.title} | CoFund` : 'CoFund';

    if (to.meta.requiresAuth) {
        const token = localStorage.getItem('auth_token');
        if (!token) {
            next({ name: 'login', query: { redirect: to.fullPath } });
            return;
        }
    }

    next();
});

export default router;
