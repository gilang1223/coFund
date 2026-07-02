<template>
    <div class="container-page animate-fade-in">
        <div class="mb-8">
            <h1 class="text-2xl md:text-3xl font-bold text-white">Donasi Saya</h1>
            <p class="text-gray-500 mt-1">Riwayat donasi yang telah Anda berikan</p>
        </div>

        <!-- Skeleton Loading -->
        <div v-if="isLoading" class="space-y-4">
            <div v-for="i in 4" :key="i" class="card p-5">
                <div class="flex gap-4">
                    <div class="skeleton h-20 w-20 rounded-md shrink-0"></div>
                    <div class="flex-1 space-y-2">
                        <div class="skeleton-title"></div>
                        <div class="skeleton-text w-1/2"></div>
                        <div class="skeleton-text w-1/3"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else-if="backings.length === 0" class="text-center py-20">
            <div class="w-16 h-16 rounded-md bg-navy-800 flex items-center justify-center mx-auto mb-4">
                <i class="pi pi-heart text-gray-600 text-2xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-300 mb-2">Belum ada donasi</h3>
            <p class="text-gray-500 mb-6">Anda belum mendukung kampanye apapun.</p>
            <router-link to="/campaigns" class="btn-brand inline-flex">
                <i class="pi pi-search mr-2"></i>
                Jelajahi Kampanye
            </router-link>
        </div>

        <!-- Backing List -->
        <div v-else class="space-y-4">
            <div
                v-for="backing in backings"
                :key="backing.id"
                class="card p-5 hover:border-navy-600 transition-all duration-200"
            >
                <div class="flex flex-col sm:flex-row gap-4">
                    <!-- Campaign Thumb -->
                    <router-link
                        :to="`/campaigns/${backing.campaign?.id}`"
                        class="shrink-0"
                    >
                        <div class="w-full sm:w-24 h-24 bg-gradient-to-br from-brand-500/20 to-navy-700 rounded-md flex items-center justify-center text-navy-600">
                            <i class="pi pi-image text-3xl"></i>
                        </div>
                    </router-link>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <router-link
                                    :to="`/campaigns/${backing.campaign?.id}`"
                                    class="text-sm font-semibold text-white hover:text-brand-400 transition-colors line-clamp-1"
                                >
                                    {{ backing.campaign?.title || 'Unknown Campaign' }}
                                </router-link>
                                <p class="text-xs text-gray-500 mt-1">
                                    Donasi {{ formatCurrency(backing.amount) }}
                                </p>
                            </div>
                            <div class="shrink-0 text-right">
                                <span :class="getStatusBadgeClass(backing.status)">
                                    {{ backing.status }}
                                </span>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-3 text-xs text-gray-600">
                            <span v-if="backing.tier">
                                <i class="pi pi-gift mr-1"></i>
                                Reward: {{ backing.tier.name }}
                            </span>
                            <span>
                                <i class="pi pi-calendar mr-1"></i>
                                {{ formatDate(backing.created_at) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useBacking } from '@/composables/useBacking';
import { useCampaign } from '@/composables/useCampaign';

const { backings, isLoading, fetchMyBackings, getStatusBadgeClass } = useBacking();
const { formatCurrency, formatDate } = useCampaign();

onMounted(() => {
    fetchMyBackings();
});
</script>
