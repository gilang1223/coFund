<template>
    <div class="flex flex-col min-h-screen">
        <!-- Navbar -->
        <nav class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <router-link to="/" class="flex items-center space-x-2">
                            <i class="pi pi-heart-fill text-blue-600 text-2xl"></i>
                            <span class="text-xl font-bold text-gray-900">CoFund</span>
                        </router-link>
                        <div class="hidden md:flex ml-10 space-x-1">
                            <router-link
                                to="/campaigns"
                                class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-colors"
                                active-class="text-blue-600 bg-blue-50"
                            >
                                Campaigns
                            </router-link>
                            <router-link
                                to="/categories"
                                class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-colors"
                                active-class="text-blue-600 bg-blue-50"
                            >
                                Categories
                            </router-link>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <template v-if="appStore.isAuthenticated">
                            <router-link
                                to="/campaigns/create"
                                class="hidden md:inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
                            >
                                <i class="pi pi-plus mr-2"></i>
                                Start Campaign
                            </router-link>
                            <router-link
                                to="/dashboard"
                                class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-colors"
                                active-class="text-blue-600 bg-blue-50"
                            >
                                <i class="pi pi-user mr-1"></i>
                                {{ appStore.user?.name }}
                            </router-link>
                            <button
                                @click="handleLogout"
                                class="p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition-colors"
                            >
                                <i class="pi pi-sign-out"></i>
                            </button>
                        </template>
                        <template v-else>
                            <router-link
                                to="/login"
                                class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 transition-colors"
                            >
                                Login
                            </router-link>
                            <router-link
                                to="/register"
                                class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
                            >
                                Register
                            </router-link>
                        </template>
                        <!-- Mobile menu button -->
                        <button
                            @click="isMobileMenuOpen = !isMobileMenuOpen"
                            class="md:hidden p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100"
                        >
                            <i :class="['pi', isMobileMenuOpen ? 'pi-times' : 'pi-bars']"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile menu -->
            <transition name="slide">
                <div v-if="isMobileMenuOpen" class="md:hidden border-t border-gray-200 bg-white">
                    <div class="px-4 py-3 space-y-2">
                        <router-link
                            to="/campaigns"
                            class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"
                            @click="isMobileMenuOpen = false"
                        >
                            Campaigns
                        </router-link>
                        <router-link
                            to="/categories"
                            class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"
                            @click="isMobileMenuOpen = false"
                        >
                            Categories
                        </router-link>
                        <router-link
                            v-if="appStore.isAuthenticated"
                            to="/campaigns/create"
                            class="block px-3 py-2 rounded-lg text-sm font-medium text-blue-600 hover:bg-blue-50"
                            @click="isMobileMenuOpen = false"
                        >
                            Start Campaign
                        </router-link>
                    </div>
                </div>
            </transition>
        </nav>

        <!-- Main Content -->
        <main class="flex-1">
            <router-view />
        </main>

        <!-- Footer -->
        <footer class="bg-gray-900 text-gray-400">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div class="col-span-1 md:col-span-2">
                        <div class="flex items-center space-x-2 mb-4">
                            <i class="pi pi-heart-fill text-blue-500 text-xl"></i>
                            <span class="text-xl font-bold text-white">CoFund</span>
                        </div>
                        <p class="text-sm leading-relaxed">
                            Platform crowdfunding lokal untuk mewujudkan ide dan proyek kreatif Anda.
                            Dukung kampanye yang Anda percaya dan jadilah bagian dari perubahan.
                        </p>
                    </div>
                    <div>
                        <h3 class="text-white font-semibold mb-3">Quick Links</h3>
                        <ul class="space-y-2 text-sm">
                            <li><router-link to="/campaigns" class="hover:text-white transition-colors">Browse Campaigns</router-link></li>
                            <li><router-link to="/categories" class="hover:text-white transition-colors">Categories</router-link></li>
                            <li><router-link to="/campaigns/create" class="hover:text-white transition-colors">Start a Campaign</router-link></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-white font-semibold mb-3">Support</h3>
                        <ul class="space-y-2 text-sm">
                            <li><a href="#" class="hover:text-white transition-colors">FAQ</a></li>
                            <li><a href="#" class="hover:text-white transition-colors">Privacy Policy</a></li>
                            <li><a href="#" class="hover:text-white transition-colors">Terms of Service</a></li>
                            <li><a href="#" class="hover:text-white transition-colors">Contact Us</a></li>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-gray-800 mt-8 pt-8 text-sm text-center">
                    &copy; {{ new Date().getFullYear() }} CoFund. All rights reserved.
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAppStore } from '@/stores/app';
import { useAuth } from '@/composables/useAuth';

const router = useRouter();
const appStore = useAppStore();
const { logout } = useAuth();
const isMobileMenuOpen = ref(false);

async function handleLogout() {
    await logout();
    router.push('/');
}
</script>

<style scoped>
.slide-enter-active,
.slide-leave-active {
    transition: all 0.2s ease;
}
.slide-enter-from,
.slide-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>
