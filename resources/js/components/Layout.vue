<template>
    <div class="flex flex-col min-h-screen bg-navy-950">
        <!-- Navbar -->
        <nav class="bg-navy-900/80 backdrop-blur-lg border-b border-navy-700/50 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <div class="flex items-center">
                        <router-link to="/" class="flex items-center space-x-2.5 group">
                            <div class="w-9 h-9 rounded-md bg-brand-500/10 flex items-center justify-center group-hover:bg-brand-500/20 transition-colors">
                                <i class="pi pi-heart-fill text-brand-500 text-lg"></i>
                            </div>
                            <span class="text-xl font-bold text-white tracking-tight">CoFund</span>
                        </router-link>
                        <div class="hidden md:flex ml-10 space-x-1">
                            <router-link
                                to="/campaigns"
                                class="px-3 py-2 rounded-md text-sm font-medium text-gray-400 hover:text-white hover:bg-navy-700/50 transition-all duration-200"
                                active-class="text-brand-400 bg-brand-500/10"
                            >
                                Campaigns
                            </router-link>
                            <router-link
                                to="/categories"
                                class="px-3 py-2 rounded-md text-sm font-medium text-gray-400 hover:text-white hover:bg-navy-700/50 transition-all duration-200"
                                active-class="text-brand-400 bg-brand-500/10"
                            >
                                Categories
                            </router-link>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <template v-if="appStore.isAuthenticated">
                            <router-link
                                to="/campaigns/create"
                                class="hidden md:inline-flex items-center px-4 py-2 bg-brand-500 text-white text-sm font-medium rounded-md hover:bg-brand-600 transition-all duration-200"
                            >
                                <i class="pi pi-plus mr-2 text-xs"></i>
                                Start Campaign
                            </router-link>
                            <!-- Notification Bell -->
                            <router-link
                                to="/notifications"
                                class="relative p-2 rounded-md text-gray-500 hover:text-gray-300 hover:bg-navy-700/50 transition-all duration-200"
                                v-tooltip.bottom="'Notifikasi'"
                            >
                                <i class="pi pi-bell text-lg"></i>
                                <span
                                    v-if="unreadCount > 0"
                                    class="absolute -top-0.5 -right-0.5 w-4.5 h-4.5 rounded-full bg-brand-500 text-white text-[10px] font-bold flex items-center justify-center"
                                >
                                    {{ unreadCount > 9 ? '9+' : unreadCount }}
                                </span>
                            </router-link>
                            <router-link
                                to="/dashboard"
                                class="hidden md:inline-flex px-3 py-2 rounded-md text-sm font-medium text-gray-400 hover:text-white hover:bg-navy-700/50 transition-all duration-200"
                            >
                                <i class="pi pi-user mr-1.5"></i>
                                {{ appStore.user?.name }}
                            </router-link>
                            <button
                                @click="handleLogout"
                                class="p-2 rounded-md text-gray-500 hover:text-gray-300 hover:bg-navy-700/50 transition-all duration-200"
                                v-tooltip.left="'Logout'"
                            >
                                <i class="pi pi-sign-out"></i>
                            </button>
                        </template>
                        <template v-else>
                            <router-link
                                to="/login"
                                class="px-4 py-2 text-sm font-medium text-gray-300 hover:text-white transition-colors duration-200"
                            >
                                Login
                            </router-link>
                            <router-link
                                to="/register"
                                class="px-4 py-2 bg-brand-500 text-white text-sm font-medium rounded-md hover:bg-brand-600 transition-all duration-200"
                            >
                                Register
                            </router-link>
                        </template>
                        <!-- Mobile menu button -->
                        <button
                            @click="isMobileMenuOpen = !isMobileMenuOpen"
                            class="md:hidden p-2 rounded-md text-gray-500 hover:text-gray-300 hover:bg-navy-700/50 transition-all duration-200"
                        >
                            <i :class="['pi text-lg', isMobileMenuOpen ? 'pi-times' : 'pi-bars']"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile menu -->
            <transition name="mobile-menu">
                <div v-if="isMobileMenuOpen" class="md:hidden border-t border-navy-700/50 bg-navy-900/95 backdrop-blur-lg">
                    <div class="px-4 py-3 space-y-1">
                        <router-link
                            to="/campaigns"
                            class="block px-3 py-2.5 rounded-md text-sm font-medium text-gray-400 hover:text-white hover:bg-navy-700/50 transition-all"
                            @click="isMobileMenuOpen = false"
                        >
                            Campaigns
                        </router-link>
                        <router-link
                            to="/categories"
                            class="block px-3 py-2.5 rounded-md text-sm font-medium text-gray-400 hover:text-white hover:bg-navy-700/50 transition-all"
                            @click="isMobileMenuOpen = false"
                        >
                            Categories
                        </router-link>
                        <template v-if="appStore.isAuthenticated">
                            <router-link
                                to="/campaigns/create"
                                class="block px-3 py-2.5 rounded-md text-sm font-medium text-brand-400 hover:bg-brand-500/10 transition-all"
                                @click="isMobileMenuOpen = false"
                            >
                                <i class="pi pi-plus mr-2 text-xs"></i>
                                Start Campaign
                            </router-link>
                            <router-link
                                to="/dashboard"
                                class="block px-3 py-2.5 rounded-md text-sm font-medium text-gray-400 hover:text-white hover:bg-navy-700/50 transition-all"
                                @click="isMobileMenuOpen = false"
                            >
                                <i class="pi pi-user mr-1.5"></i>
                                Dashboard
                            </router-link>
                        </template>
                        <template v-else>
                            <router-link
                                to="/login"
                                class="block px-3 py-2.5 rounded-md text-sm font-medium text-gray-400 hover:text-white hover:bg-navy-700/50 transition-all"
                                @click="isMobileMenuOpen = false"
                            >
                                Login
                            </router-link>
                            <router-link
                                to="/register"
                                class="block px-3 py-2.5 rounded-md text-sm font-medium text-brand-400 hover:bg-brand-500/10 transition-all"
                                @click="isMobileMenuOpen = false"
                            >
                                Register
                            </router-link>
                        </template>
                    </div>
                </div>
            </transition>
        </nav>

        <!-- Main Content -->
        <main class="flex-1">
            <router-view />
        </main>

        <!-- Footer -->
        <footer class="border-t border-navy-700/30 bg-navy-950">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div class="col-span-1 md:col-span-2">
                        <router-link to="/" class="flex items-center space-x-2.5 mb-4">
                            <div class="w-8 h-8 rounded-md bg-brand-500/10 flex items-center justify-center">
                                <i class="pi pi-heart-fill text-brand-500 text-sm"></i>
                            </div>
                            <span class="text-lg font-bold text-white">CoFund</span>
                        </router-link>
                        <p class="text-sm text-gray-500 leading-relaxed max-w-md">
                            Platform crowdfunding lokal untuk mewujudkan ide dan proyek kreatif Anda.
                            Dukung kampanye yang Anda percaya dan jadilah bagian dari perubahan.
                        </p>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-300 uppercase tracking-wider mb-4">Quick Links</h3>
                        <ul class="space-y-3">
                            <li><router-link to="/campaigns" class="text-sm text-gray-500 hover:text-gray-300 transition-colors">Browse Campaigns</router-link></li>
                            <li><router-link to="/categories" class="text-sm text-gray-500 hover:text-gray-300 transition-colors">Categories</router-link></li>
                            <li><router-link to="/campaigns/create" class="text-sm text-gray-500 hover:text-gray-300 transition-colors">Start a Campaign</router-link></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-300 uppercase tracking-wider mb-4">Support</h3>
                        <ul class="space-y-3">
                            <li><a href="#" class="text-sm text-gray-500 hover:text-gray-300 transition-colors">FAQ</a></li>
                            <li><a href="#" class="text-sm text-gray-500 hover:text-gray-300 transition-colors">Privacy Policy</a></li>
                            <li><a href="#" class="text-sm text-gray-500 hover:text-gray-300 transition-colors">Terms of Service</a></li>
                            <li><a href="#" class="text-sm text-gray-500 hover:text-gray-300 transition-colors">Contact Us</a></li>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-navy-700/30 mt-10 pt-8 text-sm text-center text-gray-600">
                    &copy; {{ new Date().getFullYear() }} CoFund. All rights reserved.
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAppStore } from '@/stores/app';
import { useAuth } from '@/composables/useAuth';
import { notificationApi } from '@/services/api';

const router = useRouter();
const appStore = useAppStore();
const { logout } = useAuth();
const isMobileMenuOpen = ref(false);
const unreadCount = ref(0);
let pollInterval = null;

async function fetchUnreadCount() {
    if (!appStore.isAuthenticated) {
        unreadCount.value = 0;
        return;
    }
    try {
        const res = await notificationApi.getUnreadCount();
        unreadCount.value = res.data.data.unread_count;
    } catch {
        unreadCount.value = 0;
    }
}

onMounted(() => {
    fetchUnreadCount();
    // Poll every 30 seconds
    pollInterval = setInterval(fetchUnreadCount, 30000);
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
});

async function handleLogout() {
    await logout();
    router.push('/');
}
</script>

<style scoped>
.mobile-menu-enter-active,
.mobile-menu-leave-active {
    transition: all 0.2s ease-out;
}
.mobile-menu-enter-from,
.mobile-menu-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}
</style>
