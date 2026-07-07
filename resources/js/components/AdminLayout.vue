<template>
    <div class="flex flex-col min-h-screen bg-navy-950">
        <!-- Admin Navbar -->
        <nav class="bg-navy-900/80 backdrop-blur-lg border-b border-navy-700/50 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <!-- Left section -->
                    <div class="flex items-center min-w-0">
                        <router-link to="/admin" class="flex items-center space-x-2.5 group shrink-0">
                            <div class="w-9 h-9 rounded-md bg-orange-500/10 flex items-center justify-center group-hover:bg-orange-500/20 transition-colors">
                                <i class="pi pi-shield text-orange-400 text-lg"></i>
                            </div>
                            <span class="text-xl font-bold text-white tracking-tight">CoFund</span>
                            <span class="hidden sm:inline text-xs font-medium text-orange-400 bg-orange-500/10 px-2 py-0.5 rounded-sm ml-1">Admin</span>
                        </router-link>
                        <!-- Desktop Nav Links -->
                        
                    </div>

                    <!-- Right section -->
                    <div class="flex items-center space-x-2 shrink-0">
                        <!-- Notification Bell -->
                        <router-link
                            to="/notifications"
                            class="relative p-2 rounded-md text-gray-500 hover:text-gray-300 hover:bg-navy-700/50 transition-all duration-200"
                            v-tooltip.bottom="'Notifikasi'"
                        >
                            <i class="pi pi-bell text-lg"></i>
                            <span
                                v-if="unreadCount > 0"
                                class="absolute -top-0.5 -right-0.5 w-4.5 h-4.5 rounded-full bg-orange-500 text-white text-[10px] font-bold flex items-center justify-center"
                            >
                                {{ unreadCount > 9 ? '9+' : unreadCount }}
                            </span>
                        </router-link>

                        <!-- Theme Toggle -->
                        <button
                            @click="toggleTheme"
                            class="p-2 rounded-md text-gray-500 hover:text-gray-300 hover:bg-navy-700/50 transition-all duration-200"
                            v-tooltip.bottom="isDarkMode ? 'Mode Terang' : 'Mode Gelap'"
                        >
                            <i :class="['text-lg', isDarkMode ? 'pi pi-sun' : 'pi pi-moon']"></i>
                        </button>
                        <!-- Profile -->
                        <router-link
                            to="/profile"
                            class="hidden md:inline-flex px-3 py-2 rounded-md text-sm font-medium text-gray-400 hover:text-white hover:bg-navy-700/50 transition-all duration-200"
                            v-tooltip.bottom="'Profil Saya'"
                        >
                            <i class="pi pi-user mr-1.5"></i>
                            {{ appStore.user?.name }}
                        </router-link>

                        <!-- Logout -->
                        <button
                            @click="handleLogout"
                            class="p-2 rounded-md text-gray-500 hover:text-gray-300 hover:bg-navy-700/50 transition-all duration-200"
                            v-tooltip.left="'Logout'"
                        >
                            <i class="pi pi-sign-out"></i>
                        </button>

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
                            v-for="item in navItems"
                            :key="item.to"
                            :to="item.to"
                            class="block px-3 py-2.5 rounded-md text-sm font-medium transition-all"
                            :class="isActive(item.to)
                                ? 'text-orange-400 bg-orange-500/10'
                                : 'text-gray-400 hover:text-white hover:bg-navy-700/50'"
                            @click="isMobileMenuOpen = false"
                        >
                            <i :class="[item.icon, 'mr-2']"></i>
                            {{ item.label }}
                            <span
                                v-if="item.badge && item.badge > 0"
                                class="ml-2 px-1.5 py-0.5 text-xs rounded-full"
                                :class="item.badgeClass || 'bg-orange-500/20 text-orange-400'"
                            >{{ item.badge }}</span>
                        </router-link>
                        <hr class="border-navy-700/50 my-2">
                        <router-link
                            to="/profile"
                            class="block px-3 py-2.5 rounded-md text-sm font-medium text-gray-400 hover:text-white hover:bg-navy-700/50 transition-all"
                            @click="isMobileMenuOpen = false"
                        >
                            <i class="pi pi-user mr-2"></i>
                            Profil
                        </router-link>
                        <button
                            @click="handleLogoutMobile"
                            class="block w-full text-left px-3 py-2.5 rounded-md text-sm font-medium text-gray-400 hover:text-white hover:bg-navy-700/50 transition-all"
                        >
                            <i class="pi pi-sign-out mr-2"></i>
                            Logout
                        </button>
                    </div>
                </div>
            </transition>
        </nav>

        <!-- Main Content -->
        <main class="flex-1">
            <router-view />
        </main>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAppStore } from '@/stores/app';
import { useAuth } from '@/composables/useAuth';
import { useTheme } from '@/composables/useTheme';
import { notificationApi, adminApi } from '@/services/api';

const router = useRouter();
const route = useRoute();
const appStore = useAppStore();
const { logout } = useAuth();
const isMobileMenuOpen = ref(false);
const unreadCount = ref(0);
let pollInterval = null;

function isActive(to) {
    if (to === '/admin') {
        return route.path === '/admin' || route.path === '/admin/';
    }
    return route.path.startsWith(to);
}

async function fetchUnreadCount() {
    try {
        const res = await notificationApi.getUnreadCount();
        unreadCount.value = res.data.data.unread_count;
    } catch {
        unreadCount.value = 0;
    }
}

async function updateNavBadges() {
    try {
        // Fetch creator requests
        const reqsRes = await adminApi.getCreatorRequests({ status: 'pending', per_page: 1 });
        const reqsCount = reqsRes.data.meta?.total ?? 0;
        navItems.value[4].badge = reqsCount;

        // Fetch pending campaigns count
        const pendingRes = await adminApi.getPendingReviews({ per_page: 1 });
        const pendingCount = pendingRes.data.meta?.total ?? 0;
        navItems.value[1].badge = pendingCount;
    } catch (err) {
        // ignore
    }
}

onMounted(() => {
    fetchUnreadCount();
    updateNavBadges();
    pollInterval = setInterval(() => {
        fetchUnreadCount();
        updateNavBadges();
    }, 30000);
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
});

async function handleLogout() {
    await logout();
    router.push('/');
}

function handleLogoutMobile() {
    isMobileMenuOpen = false;
    handleLogout();
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
