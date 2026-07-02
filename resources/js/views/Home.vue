<template>
    <div>
        <!-- Hero Section -->
        <section class="bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-28">
                <div class="max-w-3xl mx-auto text-center">
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6 leading-tight">
                        Wujudkan Ide Kreatifmu
                    </h1>
                    <p class="text-lg md:text-xl text-blue-100 mb-10 leading-relaxed">
                        Platform crowdfunding lokal untuk mewujudkan proyek dan ide kreatif Anda.
                        Dukung kampanye yang Anda percaya dan jadilah bagian dari perubahan.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <router-link
                            to="/campaigns"
                            class="inline-flex items-center justify-center px-8 py-4 bg-white text-blue-700 font-semibold rounded-xl hover:bg-blue-50 transition-all shadow-lg hover:shadow-xl"
                        >
                            <i class="pi pi-search mr-2"></i>
                            Browse Campaigns
                        </router-link>
                        <router-link
                            to="/campaigns/create"
                            class="inline-flex items-center justify-center px-8 py-4 bg-blue-500 text-white font-semibold rounded-xl hover:bg-blue-400 transition-all border-2 border-blue-400"
                        >
                            <i class="pi pi-plus-circle mr-2"></i>
                            Start a Campaign
                        </router-link>
                    </div>
                </div>
            </div>
            <div class="h-8 bg-gradient-to-t from-gray-50"></div>
        </section>

        <!-- Stats Section -->
        <section class="bg-white -mt-8 relative z-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-8 text-center">
                        <i class="pi pi-heart text-blue-600 text-4xl mb-4"></i>
                        <div class="text-3xl font-bold text-gray-900 mb-1">Rp 0</div>
                        <div class="text-gray-600">Total Terkumpul</div>
                    </div>
                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-2xl p-8 text-center">
                        <i class="pi pi-check-circle text-green-600 text-4xl mb-4"></i>
                        <div class="text-3xl font-bold text-gray-900 mb-1">0</div>
                        <div class="text-gray-600">Kampanye Sukses</div>
                    </div>
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-2xl p-8 text-center">
                        <i class="pi pi-users text-purple-600 text-4xl mb-4"></i>
                        <div class="text-3xl font-bold text-gray-900 mb-1">0</div>
                        <div class="text-gray-600">Total Pendukung</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured Campaigns -->
        <section class="py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 class="text-2xl md:text-3xl font-bold text-gray-900">Kampanye Terbaru</h2>
                        <p class="text-gray-600 mt-1">Temukan dan dukung kampanye inspiratif</p>
                    </div>
                    <router-link
                        to="/campaigns"
                        class="text-blue-600 hover:text-blue-700 font-medium flex items-center"
                    >
                        Lihat Semua
                        <i class="pi pi-arrow-right ml-2"></i>
                    </router-link>
                </div>

                <div v-if="isLoading" class="flex justify-center py-12">
                    <i class="pi pi-spin pi-spinner text-4xl text-blue-600"></i>
                </div>

                <div v-else-if="campaigns.length === 0" class="text-center py-16">
                    <i class="pi pi-inbox text-gray-300 text-6xl mb-4"></i>
                    <p class="text-gray-500 text-lg">Belum ada kampanye tersedia</p>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="campaign in campaigns"
                        :key="campaign.id"
                        class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-shadow group"
                    >
                        <router-link :to="`/campaigns/${campaign.id}`">
                            <div class="h-48 bg-gradient-to-br from-blue-400 to-blue-600 relative overflow-hidden">
                                <div class="absolute inset-0 flex items-center justify-center text-white/30">
                                    <i class="pi pi-image text-6xl"></i>
                                </div>
                                <div class="absolute bottom-3 left-3">
                                    <span
                                        class="px-3 py-1 bg-white/90 text-xs font-semibold rounded-full text-blue-700"
                                    >
                                        {{ campaign.category?.name || 'General' }}
                                    </span>
                                </div>
                            </div>
                        </router-link>
                        <div class="p-5">
                            <router-link :to="`/campaigns/${campaign.id}`">
                                <h3 class="font-semibold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors line-clamp-2">
                                    {{ campaign.title }}
                                </h3>
                            </router-link>
                            <p class="text-sm text-gray-500 mb-4 line-clamp-2">
                                {{ campaign.description }}
                            </p>
                            <div class="space-y-3">
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div
                                        class="bg-blue-600 h-2.5 rounded-full transition-all"
                                        :style="{ width: `${getProgress(campaign.collected_amount, campaign.target_amount)}%` }"
                                    ></div>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ formatCurrency(campaign.collected_amount) }}</p>
                                        <p class="text-gray-500">terkumpul</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-semibold text-gray-900">{{ getDaysRemaining(campaign.deadline) }} hari</p>
                                        <p class="text-gray-500">tersisa</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works -->
        <section class="bg-gray-50 py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-900">Bagaimana Cara Kerjanya?</h2>
                    <p class="text-gray-600 mt-2">Mulai dalam 3 langkah mudah</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <i class="pi pi-file-edit text-blue-600 text-2xl"></i>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-2">1. Buat Kampanye</h3>
                        <p class="text-gray-500 text-sm leading-relaxed">
                            Ceritakan ide dan tujuan Anda. Tambahkan gambar, video, dan reward tiers.
                        </p>
                    </div>
                    <div class="text-center">
                        <div class="w-16 h-16 bg-green-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <i class="pi pi-share-alt text-green-600 text-2xl"></i>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-2">2. Bagikan</h3>
                        <p class="text-gray-500 text-sm leading-relaxed">
                            Sebarkan kampanye Anda ke teman, keluarga, dan media sosial.
                        </p>
                    </div>
                    <div class="text-center">
                        <div class="w-16 h-16 bg-purple-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <i class="pi pi-verified text-purple-600 text-2xl"></i>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-2">3. Wujudkan</h3>
                        <p class="text-gray-500 text-sm leading-relaxed">
                            Kumpulkan dana dan wujudkan proyek impian Anda bersama komunitas.
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useCampaign } from '@/composables/useCampaign';

const {
    campaigns,
    isLoading,
    fetchCampaigns,
    formatCurrency,
    getProgress,
    getDaysRemaining,
} = useCampaign();

onMounted(() => {
    fetchCampaigns({ status: 'active' });
});
</script>
