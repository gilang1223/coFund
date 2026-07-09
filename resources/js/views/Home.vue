<template>
    <div class="animate-fade-in">
        <!-- Hero Section -->
        <section ref="heroSection" class="relative overflow-hidden min-h-[80vh] flex items-center">
            <!-- Background Image Container (parallax) → no scale, just translate -->
            <div class="absolute inset-0 overflow-hidden">
                <div
                    class="absolute -top-48 -bottom-48 inset-x-0 will-change-transform"
                    :style="{ transform: `translateY(${parallaxOffset}px)` }"
                >
                    <img
                        src="/images/background_hero.png"
                        alt="Hero background"
                        class="w-full h-full object-cover object-center"
                    />
                    <!-- Dark Overlay -->
                    <div class="absolute inset-0 bg-gradient-to-r from-navy-950/95 via-navy-950/85 to-navy-950/70 hero-overlay-x"></div>
                    <div class="absolute inset-0 bg-gradient-to-t from-navy-950 via-transparent to-navy-950/30 hero-overlay-y"></div>
                </div>
            </div>

            <!-- Decorative elements -->
            <div class="absolute top-0 right-0 w-96 h-96 bg-brand-500/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-brand-500/5 rounded-full blur-3xl"></div>

            <!-- Hero Content -->
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 md:py-32 w-full">
                <div class="max-w-3xl mx-auto text-center">
                    <!-- Badge -->
                    <div
                        class="hero-fade-in hero-fade-in-1 inline-flex items-center px-3 py-1.5 rounded-sm bg-brand-500/10 border border-brand-500/20 mb-6"
                    >
                        <span class="w-2 h-2 rounded-full bg-brand-500 animate-pulse mr-2"></span>
                        <span class="text-xs font-medium text-brand-400">Platform Crowdfunding #1 di Indonesia</span>
                    </div>

                    <!-- Heading -->
                    <h1
                        class="hero-fade-in hero-fade-in-2 text-4xl md:text-5xl lg:text-6xl font-bold mb-6 leading-tight text-white"
                    >
                        Wujudkan Ide<br/>
                        <span class="text-brand-500">Kreatifmu</span>
                    </h1>

                    <!-- Subtitle -->
                    <p
                        class="hero-fade-in hero-fade-in-3 text-lg md:text-xl text-gray-400 mb-10 leading-relaxed max-w-2xl mx-auto"
                    >
                        Platform crowdfunding lokal untuk mewujudkan proyek dan ide kreatif Anda.
                        Dukung kampanye yang Anda percaya dan jadilah bagian dari perubahan.
                    </p>

                    <!-- Buttons -->
                    <div class="hero-fade-in hero-fade-in-4 flex flex-col sm:flex-row gap-4 justify-center">
                        <router-link
                            to="/campaigns"
                            class="inline-flex items-center justify-center px-8 py-3.5 bg-brand-500 text-white font-semibold rounded-md hover:bg-brand-600 transition-all duration-200 shadow-lg shadow-brand-500/20 hover:shadow-brand-500/30 hover:-translate-y-0.5"
                        >
                            <i class="pi pi-search mr-2.5"></i>
                            Browse Campaigns
                        </router-link>
                        <router-link
                            to="/campaigns/create"
                            class="inline-flex items-center justify-center px-8 py-3.5 bg-navy-800 text-gray-300 font-medium rounded-md hover:bg-navy-700 hover:text-white transition-all duration-200 border border-navy-700 hover:border-navy-600 hover:-translate-y-0.5"
                        >
                            <i class="pi pi-plus-circle mr-2.5"></i>
                            Start a Campaign
                        </router-link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="relative -mt-8 z-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="card p-6 text-center hover:border-navy-600 transition-colors">
                        <div class="w-12 h-12 rounded-md bg-brand-500/10 flex items-center justify-center mx-auto mb-3">
                            <i class="pi pi-heart text-brand-500 text-xl"></i>
                        </div>
                        <div class="text-2xl font-bold text-white mb-0.5">{{ formatCurrency(heroStats.total_collected) }}</div>
                        <div class="text-sm text-gray-500">Total Terkumpul</div>
                    </div>
                    <div class="card p-6 text-center hover:border-navy-600 transition-colors">
                        <div class="w-12 h-12 rounded-md bg-green-500/10 flex items-center justify-center mx-auto mb-3">
                            <i class="pi pi-check-circle text-green-400 text-xl"></i>
                        </div>
                        <div class="text-2xl font-bold text-white mb-0.5">{{ heroStats.success_count }}</div>
                        <div class="text-sm text-gray-500">Kampanye Sukses</div>
                    </div>
                    <div class="card p-6 text-center hover:border-navy-600 transition-colors">
                        <div class="w-12 h-12 rounded-md bg-brand-500/10 flex items-center justify-center mx-auto mb-3">
                            <i class="pi pi-users text-brand-400 text-xl"></i>
                        </div>
                        <div class="text-2xl font-bold text-white mb-0.5">{{ heroStats.total_backers }}</div>
                        <div class="text-sm text-gray-500">Total Pendukung</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured Campaigns -->
        <section class="py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 class="text-2xl md:text-3xl font-bold text-white">Kampanye Terbaru</h2>
                        <p class="text-gray-500 mt-1">Temukan dan dukung kampanye inspiratif</p>
                    </div>
                    <router-link
                        to="/campaigns"
                        class="text-brand-400 hover:text-brand-300 font-medium flex items-center text-sm transition-colors"
                    >
                        Lihat Semua
                        <i class="pi pi-arrow-right ml-2 text-xs"></i>
                    </router-link>
                </div>

                <!-- Skeleton Loading -->
                <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div v-for="i in 3" :key="i" class="card overflow-hidden">
                        <div class="skeleton h-48 rounded-none"></div>
                        <div class="p-5 space-y-3">
                            <div class="skeleton-title"></div>
                            <div class="skeleton-text"></div>
                            <div class="skeleton-text w-2/3"></div>
                            <div class="skeleton h-2 w-full mt-3"></div>
                        </div>
                    </div>
                </div>

                <div v-else-if="campaigns.length === 0" class="text-center py-16">
                    <div class="w-16 h-16 rounded-md bg-navy-800 flex items-center justify-center mx-auto mb-4">
                        <i class="pi pi-inbox text-gray-600 text-2xl"></i>
                    </div>
                    <p class="text-gray-500 text-lg">Belum ada kampanye tersedia</p>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div
                        v-for="campaign in campaigns"
                        :key="campaign.id"
                        class="card-hover overflow-hidden group"
                    >
                        <router-link :to="`/campaigns/${campaign.id}`">
                            <div class="h-48 bg-gradient-to-br from-brand-500/20 to-navy-800 relative overflow-hidden">
                                <img v-if="campaign.primary_image?.url && !brokenImages.has(campaign.id)" :src="campaign.primary_image.url" :alt="campaign.title" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" @error="brokenImages.add(campaign.id)" />
                                <div v-else class="absolute inset-0 flex items-center justify-center text-navy-600">
                                    <i class="pi pi-image text-6xl"></i>
                                </div>
                                <div class="absolute inset-0 bg-gradient-to-t from-navy-800 via-transparent to-transparent"></div>
                                <!-- Play button overlay for video campaigns -->
                                <div v-if="campaign.video_url" class="absolute inset-0 flex items-center justify-center">
                                    <div class="w-14 h-14 rounded-full bg-brand-500/90 flex items-center justify-center shadow-lg shadow-brand-500/30 hover:bg-brand-500 transition-all duration-200 group-hover:scale-110">
                                        <i class="pi pi-play text-white text-xl ml-1"></i>
                                    </div>
                                </div>
                                <div class="absolute bottom-3 left-3">
                                    <span class="px-2.5 py-1 bg-navy-900/80 backdrop-blur-sm text-xs font-medium rounded-sm text-gray-300 border border-navy-700/50">
                                        {{ campaign.category?.name || 'General' }}
                                    </span>
                                </div>
                            </div>
                        </router-link>
                        <div class="p-5">
                            <router-link :to="`/campaigns/${campaign.id}`">
                                <h3 class="font-semibold text-white mb-2 group-hover:text-brand-400 transition-colors line-clamp-2 text-sm break-words">
                                    {{ campaign.title }}
                                </h3>
                            </router-link>
                            <p class="text-sm text-gray-500 mb-4 line-clamp-2 break-words">
                                {{ campaign.description }}
                            </p>
                            <div class="space-y-2.5">
                                <div class="progress-bar">
                                    <div
                                        class="progress-fill"
                                        :style="{ width: `${getProgress(campaign.collected_amount, campaign.target_amount)}%` }"
                                    ></div>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <div>
                                        <p class="font-semibold text-gray-200">{{ formatCurrency(campaign.collected_amount) }}</p>
                                        <p class="text-gray-600">terkumpul</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-semibold text-gray-200">{{ getDaysRemaining(campaign.deadline) }} hari</p>
                                        <p class="text-gray-600">tersisa</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works -->
        <section class="border-t border-navy-700/30 py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-2xl md:text-3xl font-bold text-white">Bagaimana Cara Kerjanya?</h2>
                    <p class="text-gray-500 mt-2">Mulai dalam 3 langkah mudah</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="card p-8 text-center hover:border-navy-600 transition-colors">
                        <div class="w-14 h-14 rounded-md bg-brand-500/10 flex items-center justify-center mx-auto mb-4">
                            <i class="pi pi-file-edit text-brand-500 text-2xl"></i>
                        </div>
                        <h3 class="font-semibold text-white mb-2">1. Buat Kampanye</h3>
                        <p class="text-gray-500 text-sm leading-relaxed">
                            Ceritakan ide dan tujuan Anda. Tambahkan gambar, video, dan reward tiers.
                        </p>
                    </div>
                    <div class="card p-8 text-center hover:border-navy-600 transition-colors">
                        <div class="w-14 h-14 rounded-md bg-green-500/10 flex items-center justify-center mx-auto mb-4">
                            <i class="pi pi-share-alt text-green-400 text-2xl"></i>
                        </div>
                        <h3 class="font-semibold text-white mb-2">2. Bagikan</h3>
                        <p class="text-gray-500 text-sm leading-relaxed">
                            Sebarkan kampanye Anda ke teman, keluarga, dan media sosial.
                        </p>
                    </div>
                    <div class="card p-8 text-center hover:border-navy-600 transition-colors">
                        <div class="w-14 h-14 rounded-md bg-brand-500/10 flex items-center justify-center mx-auto mb-4">
                            <i class="pi pi-verified text-brand-500 text-2xl"></i>
                        </div>
                        <h3 class="font-semibold text-white mb-2">3. Wujudkan</h3>
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
import { ref, onMounted, onUnmounted } from 'vue';
import { useCampaign } from '@/composables/useCampaign';
import { campaignApi } from '@/services/api';

const {
    campaigns,
    isLoading,
    fetchCampaigns,
    formatCurrency,
    getProgress,
    getDaysRemaining,
} = useCampaign();

const brokenImages = ref(new Set());

const heroStats = ref({
    total_collected: 0,
    success_count: 0,
    total_backers: 0,
});

// Parallax
const heroSection = ref(null);
const parallaxOffset = ref(0);
let ticking = false;
let rafId = null;

function updateParallax() {
    if (!heroSection.value) return;
    const rect = heroSection.value.getBoundingClientRect();
    const windowHeight = window.innerHeight;

    // Hitung jarak hero dari center viewport
    const heroCenter = rect.top + rect.height / 2;
    const viewportCenter = windowHeight / 2;
    const distFromCenter = (heroCenter - viewportCenter) / (windowHeight / 2);
    // Clamp dan inverse untuk natural feel
    const clamped = Math.max(-1, Math.min(1, distFromCenter));
    // Offset: -50px to +50px
    parallaxOffset.value = clamped * -50;
}

function onScroll() {
    if (!ticking) {
        rafId = requestAnimationFrame(() => {
            updateParallax();
            ticking = false;
        });
        ticking = true;
    }
}

onMounted(async () => {
    await fetchCampaigns({ status: 'active' });
    // Fetch hero stats — hanya jika user sudah login
    const token = localStorage.getItem('auth_token');
    if (token) {
        try {
            const res = await campaignApi.getDashboardStats();
            if (res.data?.data) {
                heroStats.value = {
                    total_collected: res.data.data.total_collected || 0,
                    success_count: res.data.data.success_campaigns || 0,
                    total_backers: res.data.data.total_backers || 0,
                };
            }
        } catch {
            // Use defaults
        }
    }

    // Init parallax position & listener
    updateParallax();
    window.addEventListener('scroll', onScroll, { passive: true });
});

onUnmounted(() => {
    window.removeEventListener('scroll', onScroll);
    if (rafId) cancelAnimationFrame(rafId);
});
</script>

<style scoped>
/* Hero fade-in staggered animations */
.hero-fade-in {
    opacity: 0;
    transform: translateY(24px);
    animation: heroFadeIn 0.7s ease-out forwards;
}

.hero-fade-in-1 { animation-delay: 0.1s; }
.hero-fade-in-2 { animation-delay: 0.3s; }
.hero-fade-in-3 { animation-delay: 0.5s; }
.hero-fade-in-4 { animation-delay: 0.7s; }

@keyframes heroFadeIn {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
