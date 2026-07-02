<template>
    <div class="container-page animate-fade-in">
        <div class="mb-8">
            <h1 class="text-2xl md:text-3xl font-bold text-white">Dashboard</h1>
            <p class="text-gray-500 mt-1">Selamat datang, {{ appStore.user?.name }}</p>
        </div>

        <!-- Skeleton -->
        <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <div v-for="i in 3" :key="i" class="card p-6">
                <div class="skeleton h-12 w-12 rounded-md mb-4"></div>
                <div class="skeleton h-8 w-24 mb-2"></div>
                <div class="skeleton h-4 w-20"></div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div v-else-if="stats" class="space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                <router-link
                    to="/my-campaigns"
                    class="card p-6 hover:border-brand-500/30 hover:shadow-card transition-all duration-200 group"
                >
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-md bg-brand-500/10 flex items-center justify-center group-hover:bg-brand-500/20 transition-colors">
                            <i class="pi pi-verified text-brand-500 text-xl"></i>
                        </div>
                        <i class="pi pi-arrow-right text-gray-600 group-hover:text-brand-400 transition-colors text-xs"></i>
                    </div>
                    <p class="text-2xl font-bold text-white">{{ stats.campaigns_count }}</p>
                    <p class="text-gray-500 text-sm">Kampanye Saya</p>
                    <p v-if="stats.active_campaigns > 0" class="text-xs text-green-400 mt-1">
                        {{ stats.active_campaigns }} aktif
                    </p>
                </router-link>

                <router-link
                    to="/my-backings"
                    class="card p-6 hover:border-brand-500/30 hover:shadow-card transition-all duration-200 group"
                >
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-md bg-green-500/10 flex items-center justify-center group-hover:bg-green-500/20 transition-colors">
                            <i class="pi pi-heart text-green-400 text-xl"></i>
                        </div>
                        <i class="pi pi-arrow-right text-gray-600 group-hover:text-brand-400 transition-colors text-xs"></i>
                    </div>
                    <p class="text-2xl font-bold text-white">{{ stats.backings_count }}</p>
                    <p class="text-gray-500 text-sm">Donasi Saya</p>
                    <p v-if="stats.total_backed > 0" class="text-xs text-gray-400 mt-1">
                        Total: {{ formatCurrency(stats.total_backed) }}
                    </p>
                </router-link>

                <div class="card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-md bg-brand-500/10 flex items-center justify-center">
                            <i class="pi pi-wallet text-brand-400 text-xl"></i>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-white">{{ formatCurrency(stats.user?.balance || 0) }}</p>
                    <p class="text-gray-500 text-sm">Saldo</p>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Aksi Cepat</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <router-link
                        to="/my-campaigns"
                        class="flex items-center gap-3 px-4 py-3 rounded-md bg-navy-800/50 hover:bg-navy-700/50 border border-navy-700 hover:border-brand-500/30 transition-all duration-200"
                    >
                        <i class="pi pi-verified text-brand-400"></i>
                        <span class="text-sm text-gray-300">Kelola Kampanye</span>
                    </router-link>
                    <router-link
                        to="/my-backings"
                        class="flex items-center gap-3 px-4 py-3 rounded-md bg-navy-800/50 hover:bg-navy-700/50 border border-navy-700 hover:border-brand-500/30 transition-all duration-200"
                    >
                        <i class="pi pi-heart text-green-400"></i>
                        <span class="text-sm text-gray-300">Riwayat Donasi</span>
                    </router-link>
                    <router-link
                        to="/campaigns/create"
                        class="flex items-center gap-3 px-4 py-3 rounded-md bg-navy-800/50 hover:bg-navy-700/50 border border-navy-700 hover:border-brand-500/30 transition-all duration-200"
                    >
                        <i class="pi pi-plus-circle text-brand-400"></i>
                        <span class="text-sm text-gray-300">Buat Kampanye</span>
                    </router-link>
                    <router-link
                        to="/transactions"
                        class="flex items-center gap-3 px-4 py-3 rounded-md bg-navy-800/50 hover:bg-navy-700/50 border border-navy-700 hover:border-brand-500/30 transition-all duration-200"
                    >
                        <i class="pi pi-receipt text-purple-400"></i>
                        <span class="text-sm text-gray-300">Riwayat Transaksi</span>
                    </router-link>
                    <router-link
                        to="/notifications"
                        class="flex items-center gap-3 px-4 py-3 rounded-md bg-navy-800/50 hover:bg-navy-700/50 border border-navy-700 hover:border-brand-500/30 transition-all duration-200"
                    >
                        <i class="pi pi-bell text-yellow-400"></i>
                        <span class="text-sm text-gray-300">Notifikasi</span>
                    </router-link>
                    <router-link
                        to="/campaigns"
                        class="flex items-center gap-3 px-4 py-3 rounded-md bg-navy-800/50 hover:bg-navy-700/50 border border-navy-700 hover:border-brand-500/30 transition-all duration-200"
                    >
                        <i class="pi pi-search text-gray-400"></i>
                        <span class="text-sm text-gray-300">Jelajahi</span>
                    </router-link>
                </div>
            </div>

            <!-- Last Campaigns / Activity -->
            <div v-if="stats.campaigns_count > 0" class="card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-white">Ringkasan</h2>
                    <span class="text-xs text-gray-500">
                        Total terkumpul: {{ formatCurrency(stats.total_collected) }}
                    </span>
                </div>
                <div class="space-y-3">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-400">Kampanye Aktif</span>
                        <span class="text-white font-semibold">{{ stats.active_campaigns }}</span>
                    </div>
                    <div class="progress-bar h-1.5">
                        <div
                            class="progress-fill h-1.5"
                            :style="{ width: `${stats.campaigns_count > 0 ? (stats.active_campaigns / stats.campaigns_count) * 100 : 0}%` }"
                        ></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Error -->
        <div v-else-if="error" class="text-center py-10">
            <p class="text-orange-400">{{ error }}</p>
        </div>
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useAppStore } from '@/stores/app';
import { useBacking } from '@/composables/useBacking';
import { useCampaign } from '@/composables/useCampaign';

const appStore = useAppStore();
const { stats, isLoading, error, fetchDashboardStats } = useBacking();
const { formatCurrency } = useCampaign();

onMounted(() => {
    fetchDashboardStats();
});
</script>
