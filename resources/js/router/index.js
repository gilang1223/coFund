import { createRouter, createWebHistory } from 'vue-router';
import Home from '@/views/Home.vue';
import Layout from '@/components/Layout.vue';
import AdminLayout from '@/components/AdminLayout.vue';

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
                meta: { title: 'Buat Campaign', requiresAuth: true, requiresCreator: true },
            },
            {
                path: 'campaigns/:id/edit',
                name: 'campaign-edit',
                component: () => import('@/views/CampaignEdit.vue'),
                meta: { title: 'Edit Campaign', requiresAuth: true, requiresCreator: true },
            },
            {
                path: 'my-campaigns',
                name: 'my-campaigns',
                component: () => import('@/views/MyCampaigns.vue'),
                meta: { title: 'Kampanye Saya', requiresAuth: true, requiresCreator: true },
            },
            {
                path: 'my-backings',
                name: 'my-backings',
                component: () => import('@/views/MyBackings.vue'),
                meta: { title: 'Donasi Saya', requiresAuth: true, notAdmin: true },
            },
            {
                path: 'transactions',
                name: 'transactions',
                component: () => import('@/views/MyTransactions.vue'),
                meta: { title: 'Transaksi', requiresAuth: true, notAdmin: true },
            },
            {
                path: 'notifications',
                name: 'notifications',
                component: () => import('@/views/Notifications.vue'),
                meta: { title: 'Notifikasi', requiresAuth: true },
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
                meta: { title: 'Dashboard', requiresAuth: true, notAdmin: true },
            },
            {
                path: 'profile',
                name: 'profile',
                component: () => import('@/views/UserProfile.vue'),
                meta: { title: 'Profil Saya', requiresAuth: true },
            },
            {
                path: 'forgot-password',
                name: 'forgot-password',
                component: () => import('@/views/ForgotPassword.vue'),
                meta: { title: 'Lupa Password' },
            },
            {
                path: 'reset-password',
                name: 'reset-password',
                component: () => import('@/views/ResetPassword.vue'),
                meta: { title: 'Reset Password' },
            },
            {
                path: 'verify-email',
                name: 'verify-email',
                component: () => import('@/views/EmailVerification.vue'),
                meta: { title: 'Verifikasi Email', requiresAuth: true },
            },
            {
                path: 'support-chat',
                name: 'support-chat',
                component: () => import('@/views/SupportChat.vue'),
                meta: { title: 'Hubungi Admin', requiresAuth: true },
            },
            {
                path: ':pathMatch(.*)*',
                name: 'not-found',
                component: () => import('@/views/NotFound.vue'),
                meta: { title: 'Not Found' },
            },
        ],
    },
    {
        path: '/admin/login',
        name: 'admin-login',
        component: () => import('@/views/AdminLogin.vue'),
        meta: { title: 'Admin Login' },
    },
    // Admin routes — menggunakan AdminLayout terpisah
    {
        path: '/admin',
        component: AdminLayout,
        meta: { requiresAuth: true, requiresAdmin: true },
        children: [
            {
                path: '',
                name: 'admin-dashboard',
                component: () => import('@/views/AdminDashboard.vue'),
                meta: { title: 'Admin Dashboard' },
            },
            {
                path: 'pending-reviews',
                name: 'admin-pending-reviews',
                component: () => import('@/views/AdminDashboard.vue'),
                meta: { title: 'Pending Reviews' },
            },
            {
                path: 'campaigns',
                name: 'admin-campaigns',
                component: () => import('@/views/AdminDashboard.vue'),
                meta: { title: 'All Campaigns' },
            },
            {
                path: 'users',
                name: 'admin-users',
                component: () => import('@/views/AdminDashboard.vue'),
                meta: { title: 'Manage Users' },
            },
            {
                path: 'creator-requests',
                name: 'admin-creator-requests',
                component: () => import('@/views/AdminDashboard.vue'),
                meta: { title: 'Creator Requests' },
            },
            {
                path: 'support',
                name: 'admin-support',
                component: () => import('@/views/AdminDashboard.vue'),
                meta: { title: 'Support Conversations' },
            },
            {
                path: 'profile',
                name: 'admin-profile',
                component: () => import('@/views/admin/AdminProfile.vue'),
                meta: { title: 'Profil Admin' },
            },
        ],
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Navigation guard for auth & role-based access
router.beforeEach((to, from, next) => {
    document.title = to.meta.title ? `${to.meta.title} | CoFund` : 'CoFund';

    const token = localStorage.getItem('auth_token');
    const userRaw = localStorage.getItem('auth_user');
    const user = userRaw ? JSON.parse(userRaw) : null;
    const role = user?.role ?? null;

    // redirect if visiting admin login but already authenticated as admin
    if (to.name === 'admin-login' && token && role === 'admin') {
        next({ name: 'admin-dashboard' });
        return;
    }

    // Butuh login
    if (to.meta.requiresAuth && !token) {
        if (to.path.startsWith('/admin')) {
            next({ name: 'admin-login', query: { redirect: to.fullPath } });
        } else {
            next({ name: 'login', query: { redirect: to.fullPath } });
        }
        return;
    }

    // Hanya admin yang boleh akses halaman /admin/*
    if (to.meta.requiresAdmin && role !== 'admin') {
        next({ name: 'home' });
        return;
    }

    // Admin tidak boleh akses halaman user (dashboard, backings, dll)
    if (to.meta.notAdmin && role === 'admin') {
        next({ name: 'admin-dashboard' });
        return;
    }

    // Hanya creator yang boleh buat/kelola campaign
    if (to.meta.requiresCreator && role !== 'creator') {
        next({ name: 'home' });
        return;
    }

    next();
});

export default router;
