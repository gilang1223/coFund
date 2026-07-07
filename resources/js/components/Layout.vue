<template>
    <div class="flex flex-col min-h-screen bg-navy-950">
        <!-- Suspended Banner -->
        <transition name="slide-down">
            <div
                v-if="appStore.isAuthenticated && appStore.isSuspended"
                class="bg-red-500/10 border-b border-red-500/20 text-sm"
            >
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2.5 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-2 text-red-400">
                        <i class="pi pi-ban text-xs"></i>
                        <span>
                            Akun Anda sedang <strong>disuspend</strong>.
                            <router-link to="/support-chat" class="underline hover:text-red-300 font-medium">
                                Hubungi Admin
                            </router-link>
                        </span>
                    </div>
                    <router-link
                        to="/support-chat"
                        class="shrink-0 px-3 py-1 bg-red-500/20 text-red-400 text-xs font-medium rounded-md hover:bg-red-500/30 transition-all inline-flex items-center gap-1"
                    >
                        <i class="pi pi-comments"></i>
                        Chat Admin
                    </router-link>
                </div>
            </div>
        </transition>

        <!-- Email Verification Banner -->
        <transition name="slide-down">
            <div
                v-if="appStore.isAuthenticated && !appStore.hasVerifiedEmail && !appStore.isSuspended"
                class="bg-orange-500/10 border-b border-orange-500/20 text-sm"
            >
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2.5 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-2 text-orange-400">
                        <i class="pi pi-envelope text-xs"></i>
                        <span>
                            Email <strong>{{ appStore.user?.email }}</strong> belum diverifikasi.
                            <router-link to="/verify-email" class="underline hover:text-orange-300 font-medium">
                                Verifikasi sekarang
                            </router-link>
                        </span>
                    </div>
                    <button
                        @click="sendVerifEmail"
                        :disabled="isSendingVerif"
                        class="shrink-0 px-3 py-1 bg-orange-500/20 text-orange-400 text-xs font-medium rounded-md hover:bg-orange-500/30 transition-all disabled:opacity-60"
                    >
                        <i v-if="isSendingVerif" class="pi pi-spin pi-spinner mr-1"></i>
                        {{ isSendingVerif ? 'Mengirim...' : 'Kirim Email' }}
                    </button>
                </div>
            </div>
        </transition>

        <!-- Navbar -->
        <nav class="bg-navy-900/80 backdrop-blur-lg border-b border-navy-700/50 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <div class="flex items-center">
                        <router-link to="/" class="flex items-center space-x-2.5 group">
                                <img src="/images/logo_cofund.png" alt="CoFund Logo" class="w-15 h-10" />
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
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <template v-if="appStore.isAuthenticated">
                            <!-- Start Campaign: creator langsung navigasi, backer lihat modal -->
                            <button
                                v-if="!appStore.isSuspended"
                                @click="handleStartCampaign"
                                class="hidden md:inline-flex items-center px-4 py-2 bg-brand-500 text-white text-sm font-medium rounded-md hover:bg-brand-600 transition-all duration-200"
                            >
                                <i class="pi pi-plus mr-2 text-xs"></i>
                                Start Campaign
                            </button>
                            <!-- Dashboard -->
                            <router-link
                                to="/dashboard"
                                class="hidden md:inline-flex px-3 py-2 rounded-md text-sm font-medium text-gray-400 hover:text-white hover:bg-navy-700/50 transition-all duration-200"
                                active-class="text-brand-400 bg-brand-500/10"
                                v-tooltip.bottom="'Dashboard'"
                            >
                                <i class="pi pi-th-large"></i>
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
                            <!-- Profile Dropdown (hover) -->
                            <div
                                class="relative hidden md:block"
                                @mouseenter="isProfileOpen = true"
                                @mouseleave="isProfileOpen = false"
                            >
                                <button
                                    class="flex items-center gap-1.5 px-3 py-2 rounded-md text-sm font-medium text-gray-400 hover:text-white hover:bg-navy-700/50 transition-all duration-200"
                                >
                                    <i class="pi pi-user"></i>
                                    {{ appStore.user?.name }}
                                    <i :class="['pi text-xs transition-transform duration-200', isProfileOpen ? 'pi-chevron-up' : 'pi-chevron-down']"></i>
                                </button>
                                <!-- Dropdown Menu -->
                                <transition name="dropdown">
                                    <div
                                        v-if="isProfileOpen"
                                        class="absolute right-0 top-full mt-1.5 w-52 bg-navy-800 border border-navy-600 rounded-lg shadow-xl shadow-black/30 py-1 z-50"
                                        @mouseenter="isProfileOpen = true"
                                        @mouseleave="isProfileOpen = false"
                                    >
                                        <!-- Profile -->
                                        <router-link
                                            to="/profile"
                                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-300 hover:text-white hover:bg-navy-700/50 transition-colors"
                                        >
                                            <i class="pi pi-user w-4 text-center text-gray-500"></i>
                                            Profil Saya
                                        </router-link>

                                        <hr class="border-navy-600/50 mx-3" />

                                        <!-- Theme Toggle -->
                                        <button
                                            @click="toggleTheme"
                                            class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-gray-300 hover:text-white hover:bg-navy-700/50 transition-colors text-left"
                                        >
                                            <i :class="['pi w-4 text-center text-gray-500', isDarkMode ? 'pi-sun' : 'pi-moon']"></i>
                                            {{ isDarkMode ? 'Mode Terang' : 'Mode Gelap' }}
                                        </button>

                                        <hr class="border-navy-600/50 mx-3" />

                                        <!-- Logout -->
                                        <button
                                            @click="handleLogout"
                                            class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-red-400 hover:text-red-300 hover:bg-red-500/10 transition-colors text-left"
                                        >
                                            <i class="pi pi-sign-out w-4 text-center"></i>
                                            Logout
                                        </button>
                                    </div>
                                </transition>
                            </div>
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
                            <!-- Theme Toggle for non-logged-in users -->
                            <button
                                @click="toggleTheme"
                                class="p-2 rounded-md text-gray-500 hover:text-gray-300 hover:bg-navy-700/50 transition-all duration-200"
                                v-tooltip.bottom="isDarkMode ? 'Mode Terang' : 'Mode Gelap'"
                            >
                                <i :class="['text-lg', isDarkMode ? 'pi pi-sun' : 'pi pi-moon']"></i>
                            </button>
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
                <div v-if="isMobileMenuOpen" class="md:hidden border-t border-navy-700/50 bg-navy-900/95 backdrop-blur-lg">                        <div class="px-4 py-3 space-y-1">
                        <router-link
                            to="/campaigns"
                            class="block px-3 py-2.5 rounded-md text-sm font-medium text-gray-400 hover:text-white hover:bg-navy-700/50 transition-all"
                            @click="isMobileMenuOpen = false"
                        >
                            Campaigns
                        </router-link>
                        <template v-if="appStore.isAuthenticated">
                            <!-- Start Campaign: creator langsung navigasi, backer lihat modal -->
                            <button
                                v-if="!appStore.isSuspended"
                                @click="handleStartCampaignMobile"
                                class="block w-full text-left px-3 py-2.5 rounded-md text-sm font-medium text-brand-400 hover:bg-brand-500/10 transition-all"
                            >
                                <i class="pi pi-plus mr-2 text-xs"></i>
                                Start Campaign
                            </button>
                            <!-- Profile -->
                            <router-link
                                to="/profile"
                                class="block px-3 py-2.5 rounded-md text-sm font-medium text-gray-400 hover:text-white hover:bg-navy-700/50 transition-all"
                                @click="isMobileMenuOpen = false"
                            >
                                <i class="pi pi-user mr-1.5"></i>
                                Profil
                            </router-link>
                            <!-- Dashboard: hanya backer/creator -->
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
        <main class="flex-1 relative">
            <!-- Background texture overlay -->
            <div class="fixed inset-0 pointer-events-none z-0" aria-hidden="true">
                <div class="absolute inset-0 bg-glow-top"></div>
                <div class="absolute inset-0 bg-pattern-dots"></div>
            </div>
            <!-- Content above background -->
            <div class="relative z-10">
                <router-view />
            </div>
        </main>

        <!-- Creator Guard Modal -->
        <CreatorGuardModal
            v-model="showCreatorModal"
            @goToProfile="goToProfile(router)"
        />

        <!-- Footer -->
        <footer class="border-t border-navy-700/30 bg-navy-950">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div class="col-span-1 md:col-span-2">
                        <router-link to="/" class="flex items-center space-x-2.5 mb-4">
                                <img src="/images/logo_cofund.png" alt="CoFund Logo" class="w-13 h-8" />
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

                            <li v-if="!appStore.isSuspended">
                                <button @click="handleStartCampaign" class="text-sm text-gray-500 hover:text-gray-300 transition-colors bg-transparent border-0 p-0 cursor-pointer">
                                    Start a Campaign
                                </button>
                            </li>
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
                    &copy; {{ dayjs().year() }} CoFund. All rights reserved.
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
import { useCreatorGuard } from '@/composables/useCreatorGuard';
import { useTheme } from '@/composables/useTheme';
import CreatorGuardModal from '@/components/CreatorGuardModal.vue';
import { useToast } from 'vue-toastification';
import { useNotification } from '@/composables/useNotification';
import dayjs from '@/plugins/dayjs';

const router = useRouter();
const appStore = useAppStore();
const toast = useToast();
const { logout } = useAuth();
const { showCreatorModal, openCreatorModal, goToProfile } = useCreatorGuard();
const { isDarkMode, toggleTheme } = useTheme();
const { unreadCount, fetchUnreadCount } = useNotification();
const isMobileMenuOpen = ref(false);
const isProfileOpen = ref(false);
const isSendingVerif = ref(false);
let pollInterval = null;

onMounted(() => {
    fetchUnreadCount();
    // Poll every 30 seconds
    pollInterval = setInterval(fetchUnreadCount, 30000);
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
});

function handleStartCampaign() {
    if (appStore.isCreator) {
        router.push('/campaigns/create');
    } else {
        openCreatorModal();
    }
}

function handleStartCampaignMobile() {
    isMobileMenuOpen = false;
    handleStartCampaign();
}

async function handleLogout() {
    await logout();
    router.push('/');
}

async function sendVerifEmail() {
    if (isSendingVerif.value) return;
    isSendingVerif.value = true;
    try {
        const response = await fetch('/api/email/verification-notification', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
        });
        if (response.ok) {
            toast.success('Email verifikasi telah dikirim! Cek inbox atau folder spam.');
        }
    } catch {
        // Silently fail
    } finally {
        isSendingVerif.value = false;
    }
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

.dropdown-enter-active,
.dropdown-leave-active {
    transition: all 0.15s ease-out;
}
.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-4px);
}
</style>
