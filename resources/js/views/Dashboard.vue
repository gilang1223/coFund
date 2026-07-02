<template>
    <div class="container-page animate-fade-in">
        <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">Dashboard</h1>
        <p class="text-gray-500 mb-8">Selamat datang, {{ appStore.user?.name }}</p>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <div class="card p-6 hover:border-navy-600 transition-colors">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-md bg-brand-500/10 flex items-center justify-center">
                        <i class="pi pi-verified text-brand-500 text-xl"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-white">{{ stats.campaigns }}</p>
                <p class="text-gray-500 text-sm">Kampanye Saya</p>
            </div>
            <div class="card p-6 hover:border-navy-600 transition-colors">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-md bg-green-500/10 flex items-center justify-center">
                        <i class="pi pi-heart text-green-400 text-xl"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-white">{{ stats.backings }}</p>
                <p class="text-gray-500 text-sm">Donasi Saya</p>
            </div>
            <div class="card p-6 hover:border-navy-600 transition-colors">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-md bg-brand-500/10 flex items-center justify-center">
                        <i class="pi pi-wallet text-brand-400 text-xl"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-white">{{ formatBalance }}</p>
                <p class="text-gray-500 text-sm">Saldo</p>
            </div>
        </div>

        <div class="mt-8 card p-10 text-center">
            <div class="w-16 h-16 rounded-md bg-navy-700/50 flex items-center justify-center mx-auto mb-4">
                <i class="pi pi-rocket text-gray-600 text-3xl"></i>
            </div>
            <h2 class="text-xl font-semibold text-gray-200 mb-2">Fitur Dashboard Lengkap</h2>
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
