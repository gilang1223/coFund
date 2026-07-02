<template>
    <div class="container-page animate-fade-in">
        <div class="mb-8">
            <h1 class="text-2xl md:text-3xl font-bold text-white">Transaksi</h1>
            <p class="text-gray-500 mt-1">Riwayat seluruh transaksi keuangan Anda</p>
        </div>

        <!-- Escrow Flow Diagram -->
        <div class="card p-5 mb-6">
            <h3 class="text-sm font-semibold text-gray-300 mb-3">Alur Escrow</h3>
            <div class="flex flex-wrap items-center gap-2 text-xs">
                <span class="px-2.5 py-1 rounded-sm bg-brand-500/10 text-brand-400 border border-brand-500/20">Donasi</span>
                <i class="pi pi-arrow-right text-gray-600"></i>
                <span class="px-2.5 py-1 rounded-sm bg-yellow-500/10 text-yellow-400 border border-yellow-500/20">Escrow (Pending)</span>
                <i class="pi pi-arrow-right text-gray-600"></i>
                <span class="px-2.5 py-1 rounded-sm bg-green-500/10 text-green-400 border border-green-500/20">Sukses → Cair ke Creator</span>
                <span class="text-gray-600 mx-1">|</span>
                <span class="px-2.5 py-1 rounded-sm bg-orange-500/10 text-orange-400 border border-orange-500/20">Gagal → Refund ke Backer</span>
            </div>
        </div>

        <!-- Skeleton -->
        <div v-if="isLoading" class="space-y-3">
            <div v-for="i in 5" :key="i" class="card p-4">
                <div class="flex items-center gap-4">
                    <div class="skeleton h-10 w-10 rounded-md shrink-0"></div>
                    <div class="flex-1 space-y-2">
                        <div class="skeleton-text w-1/3"></div>
                        <div class="skeleton-text w-1/2"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else-if="transactions.length === 0" class="text-center py-20">
            <div class="w-16 h-16 rounded-md bg-navy-800 flex items-center justify-center mx-auto mb-4">
                <i class="pi pi-receipt text-gray-600 text-2xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-300 mb-2">Belum ada transaksi</h3>
            <p class="text-gray-500 mb-6">Anda belum melakukan transaksi apapun.</p>
            <router-link to="/campaigns" class="btn-brand inline-flex">
                <i class="pi pi-search mr-2"></i>
                Jelajahi Kampanye
            </router-link>
        </div>

        <!-- Transaction List -->
        <div v-else class="space-y-3">
            <div
                v-for="tx in transactions"
                :key="tx.id"
                class="card p-4 hover:border-navy-600 transition-all duration-200"
            >
                <div class="flex items-start gap-4">
                    <!-- Type Icon -->
                    <div
                        :class="[
                            'w-10 h-10 rounded-md flex items-center justify-center shrink-0',
                            tx.type === 'payment' ? 'bg-brand-500/10' :
                            tx.type === 'disbursement' ? 'bg-green-500/10' :
                            tx.type === 'refund' ? 'bg-orange-500/10' :
                            'bg-purple-500/10'
                        ]"
                    >
                        <i :class="['pi text-sm', getTypeIcon(tx.type), getTypeColor(tx.type)]"></i>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-medium text-white">
                                    {{ getTypeLabel(tx.type) }}
                                </p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ tx.reference }}
                                </p>
                                <p v-if="tx.backing?.campaign" class="text-xs text-gray-600 mt-0.5">
                                    {{ tx.backing.campaign.title }}
                                </p>
                            </div>
                            <div class="text-right shrink-0">
                                <p :class="[
                                    'text-sm font-semibold',
                                    tx.type === 'refund' ? 'text-green-400' :
                                    tx.type === 'disbursement' ? 'text-green-400' :
                                    tx.type === 'payment' ? 'text-brand-400' :
                                    'text-gray-300'
                                ]">
                                    {{ tx.type === 'refund' || tx.type === 'disbursement' ? '+' : '-' }}
                                    {{ formatCurrency(tx.amount) }}
                                </p>
                                <span :class="getStatusBadgeClass(tx.status)">
                                    {{ tx.status }}
                                </span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-600 mt-2">
                            <i class="pi pi-calendar mr-1"></i>
                            {{ formatDate(tx.created_at) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useTransaction } from '@/composables/useTransaction';

const {
    transactions,
    isLoading,
    fetchTransactions,
    formatCurrency,
    formatDate,
    getTypeLabel,
    getTypeIcon,
    getTypeColor,
    getStatusBadgeClass,
} = useTransaction();

onMounted(() => {
    fetchTransactions();
});
</script>
