<template>
    <div class="container-page">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">Dashboard</h1>
        <p class="text-gray-600 mb-8">Selamat datang, {{ appStore.user?.name }}</p>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                        <i class="pi pi-verified text-blue-600 text-xl"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ stats.campaigns }}</p>
                <p class="text-gray-500 text-sm">Kampanye Saya</p>
            </div>
            <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                        <i class="pi pi-heart text-green-600 text-xl"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ stats.backings }}</p>
                <p class="text-gray-500 text-sm">Donasi Saya</p>
            </div>
            <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                        <i class="pi pi-wallet text-purple-600 text-xl"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900">Rp {{ formatBalance }}</p>
                <p class="text-gray-500 text-sm">Saldo</p>
            </div>
        </div>

        <div class="mt-8 bg-white rounded-xl p-8 border border-gray-100 shadow-sm text-center">
            <i class="pi pi-rocket text-gray-300 text-5xl mb-4"></i>
            <h2 class="text-xl font-semibold text-gray-900 mb-2">Fitur Dashboard Lengkap</h2>
            <p class="text-gray-500">Halaman ini akan menampilkan statistik, histori donasi, dan manajemen kampanye.</p>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useAppStore } from '@/stores/app';

const appStore = useAppStore();

const stats = {
    campaigns: 0,
    backings: 0,
    balance: appStore.user?.balance || 0,
};

const formatBalance = computed(() => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(appStore.user?.balance || 0);
});
</script>
