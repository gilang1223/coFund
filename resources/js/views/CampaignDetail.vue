<template>
    <div class="container-page animate-fade-in">
        <div v-if="isLoading" class="space-y-4">
            <div class="skeleton h-4 w-48 mb-8"></div>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-6">
                    <div class="skeleton h-96 rounded-lg"></div>
                    <div class="skeleton-title mb-2"></div>
                    <div class="skeleton-text w-1/2"></div>
                    <div class="skeleton h-24 rounded-lg mt-6"></div>
                </div>
                <div>
                    <div class="card p-6 space-y-4">
                        <div class="skeleton h-10 w-full"></div>
                        <div class="skeleton h-3 w-full"></div>
                        <div class="skeleton h-20 w-full"></div>
                    </div>
                </div>
            </div>
        </div>

        <template v-else-if="campaign">
            <!-- Breadcrumb -->
            <nav class="text-sm text-gray-500 mb-6">
                <router-link to="/campaigns" class="hover:text-brand-400 transition-colors">Campaigns</router-link>
                <span class="mx-2 text-navy-700">/</span>
                <span class="text-gray-300">{{ campaign.title }}</span>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Video / Image -->
                    <div class="rounded-lg overflow-hidden bg-navy-900">
                        <!-- YouTube Embed -->
                        <div v-if="campaign.video_url && getYouTubeId(campaign.video_url)" class="relative w-full" style="padding-top: 56.25%;">
                            <iframe
                                :src="`https://www.youtube.com/embed/${getYouTubeId(campaign.video_url)}?rel=0&showinfo=0`"
                                class="absolute inset-0 w-full h-full"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen
                            ></iframe>
                        </div>
                        <!-- Static Image fallback -->
                        <div v-else class="h-64 md:h-96 bg-gradient-to-br from-brand-500/20 to-navy-800 flex items-center justify-center text-navy-600">
                            <img v-if="campaign.images?.length" :src="campaign.images[0].url" :alt="campaign.title" class="w-full h-full object-cover" @error="$event.target.style.display='none'" />
                            <i v-else class="pi pi-image text-8xl"></i>
                        </div>
                    </div>

                    <!-- Title & Meta -->
                    <div>
                        <div class="flex flex-wrap items-center gap-3 mb-3">
                            <span
                                :class="[
                                    campaign.status === 'active' ? 'badge-active' :
                                    campaign.status === 'success' ? 'badge-success' :
                                    campaign.status === 'draft' ? 'badge-draft' :
                                    campaign.status === 'failed' ? 'badge-failed' :
                                    'badge-default'
                                ]"
                            >
                                {{ campaign.status }}
                            </span>
                            <span class="px-2.5 py-1 bg-brand-500/10 text-xs font-medium rounded-sm text-brand-400 border border-brand-500/20">
                                {{ campaign.category?.name }}
                            </span>
                        </div>
                        <h1 class="text-2xl md:text-3xl font-bold text-white mb-2 break-words">{{ campaign.title }}</h1>
                        <p class="text-gray-500">
                            oleh <span class="font-medium text-gray-300">{{ campaign.creator?.name || 'Anonymous' }}</span>
                            &middot; Dibuat {{ formatDate(campaign.created_at) }}
                        </p>
                    </div>

                    <!-- Description -->
                    <div class="card p-6">
                        <h2 class="text-lg font-semibold text-white mb-4">Tentang Kampanye</h2>
                        <p class="text-gray-400 leading-relaxed whitespace-pre-line break-words">{{ campaign.description }}</p>
                    </div>

                    <!-- Updates -->
                    <div v-if="campaign.updates?.length" class="card p-6">
                        <h2 class="text-lg font-semibold text-white mb-4">Pembaruan</h2>
                        <div v-for="update in campaign.updates" :key="update.id" class="border-l-2 border-brand-500/30 pl-4 py-3 mb-4 last:mb-0">
                            <h3 class="font-medium text-gray-200">{{ update.title }}</h3>
                            <p class="text-sm text-gray-500 mt-1">{{ update.content }}</p>
                            <p class="text-xs text-gray-600 mt-2">{{ formatDate(update.created_at) }}</p>
                        </div>
                    </div>

                    <!-- Funding Chart (hanya untuk creator campaign ini) -->
                    <div v-if="isCreator && campaign.status !== 'draft' && campaign.status !== 'rejected'" class="card p-6">
                        <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                            <i class="pi pi-chart-bar text-brand-400 text-sm"></i>
                            Grafik Pendanaan
                        </h2>
                        <div class="relative">
                            <canvas ref="chartCanvas" class="w-full h-48"></canvas>
                        </div>
                        <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                            <div class="p-3 rounded-md bg-navy-800/50">
                                <p class="text-xs text-gray-500">Target</p>
                                <p class="text-sm font-semibold text-white">{{ formatCurrency(campaign.target_amount) }}</p>
                            </div>
                            <div class="p-3 rounded-md bg-navy-800/50">
                                <p class="text-xs text-gray-500">Terkumpul</p>
                                <p class="text-sm font-semibold text-green-400">{{ formatCurrency(campaign.collected_amount) }}</p>
                            </div>
                            <div class="p-3 rounded-md bg-navy-800/50">
                                <p class="text-xs text-gray-500">Progress</p>
                                <p class="text-sm font-semibold text-brand-400">{{ getProgress(campaign.collected_amount, campaign.target_amount) }}%</p>
                            </div>
                            <div class="p-3 rounded-md bg-navy-800/50">
                                <p class="text-xs text-gray-500">Donatur</p>
                                <p class="text-sm font-semibold text-white">{{ backingsCount }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Funding Card -->
                    <div class="card p-6 sticky top-24">
                        <div class="text-center mb-6">
                            <p class="text-3xl font-bold text-white">{{ formatCurrency(campaign.collected_amount) }}</p>
                            <p class="text-gray-500 text-sm mt-1">terkumpul dari {{ formatCurrency(campaign.target_amount) }}</p>
                        </div>

                        <div class="progress-bar h-3 mb-4">
                            <div
                                class="progress-fill h-3"
                                :style="{ width: `${getProgress(campaign.collected_amount, campaign.target_amount)}%` }"
                            ></div>
                        </div>

                        <div class="flex justify-between text-sm mb-6">
                            <div>
                                <p class="font-semibold text-gray-200">{{ getProgress(campaign.collected_amount, campaign.target_amount) }}%</p>
                                <p class="text-gray-600">tercapai</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-200">{{ getDaysRemaining(campaign.deadline) }} hari</p>
                                <p class="text-gray-600">tersisa</p>
                            </div>
                        </div>

                        <button
                            v-if="campaign.status === 'active' && !appStore.isAdmin && !appStore.isSuspended"
                            @click="showBackingDialog = true"
                            class="w-full py-3 bg-brand-500 text-white font-semibold rounded-md hover:bg-brand-600 transition-all duration-200"
                        >
                            <i class="pi pi-heart mr-2"></i>
                            Dukung Kampanye Ini
                        </button>
                        <!-- Suspended info -->
                        <div
                            v-else-if="appStore.isSuspended"
                            class="w-full py-3 bg-red-500/10 text-red-400 text-sm font-medium rounded-md text-center border border-red-500/20"
                        >
                            <i class="pi pi-ban mr-2"></i>
                            Akun disuspend
                        </div>

                        <!-- Tiers -->
                        <div v-if="campaign.tiers?.length" class="mt-6 space-y-3">
                            <h3 class="font-semibold text-gray-200">Pilih Reward</h3>
                            <div
                                v-for="tier in campaign.tiers"
                                :key="tier.id"
                                @click="selectTier(tier)"
                                :class="[
                                    'p-4 rounded-md border cursor-pointer transition-all duration-200',
                                    selectedTier?.id === tier.id
                                        ? 'border-brand-500/50 bg-brand-500/10'
                                        : 'border-navy-700 hover:border-navy-600 bg-navy-800/50'
                                ]"
                            >
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-semibold text-white text-sm">{{ tier.name }}</h4>
                                        <p class="text-sm text-gray-500 mt-1">{{ tier.reward_description }}</p>
                                    </div>
                                    <span class="text-brand-400 font-bold">{{ formatCurrency(tier.min_amount) }}</span>
                                </div>
                                <p class="text-xs text-gray-600 mt-2">
                                    {{ tier.remaining_quota }} dari {{ tier.quota }} tersisa
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Not Found -->
        <div v-else class="text-center py-20">
            <div class="w-16 h-16 rounded-md bg-navy-800 flex items-center justify-center mx-auto mb-4">
                <i class="pi pi-exclamation-circle text-gray-600 text-2xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-300">Kampanye tidak ditemukan</h3>
        </div>

        <!-- Backing Dialog -->
        <Dialog
            v-if="!appStore.isAdmin"
            v-model:visible="showBackingDialog"
            header="Dukung Kampanye"
            :modal="true"
            class="w-full max-w-md"
        >
            <div v-if="!appStore.isAuthenticated" class="text-center py-4">
                <p class="text-gray-400 mb-4">Silakan login atau daftar untuk mendukung kampanye</p>
                <router-link to="/login" class="text-brand-400 font-medium hover:text-brand-300 transition-colors">
                    Login / Register
                </router-link>
            </div>
            <div v-else class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Jumlah Donasi</label>
                    <InputNumber
                        v-model="backingAmount"
                        :min="10000"
                        :step="10000"
                        class="w-full"
                        placeholder="Min. Rp 10.000"
                        mode="currency"
                        currency="IDR"
                        locale="id-ID"
                    />
                </div>
                <div v-if="selectedTier" class="bg-brand-500/10 rounded-md p-3 border border-brand-500/20">
                    <p class="text-sm font-medium text-brand-400">Reward: {{ selectedTier.name }}</p>
                    <p class="text-xs text-brand-500/70 mt-1">Min. {{ formatCurrency(selectedTier.min_amount) }}</p>
                </div>
                <Button
                    label="Donasi Sekarang"
                    icon="pi pi-credit-card"
                    class="w-full"
                    @click="confirmBacking"
                    :loading="isProcessing"
                />
        
            </div>
        </Dialog>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAppStore } from '@/stores/app';
import { useCampaign } from '@/composables/useCampaign';
import { campaignApi, backingApi } from '@/services/api';
import Dialog from 'primevue/dialog';
import InputNumber from 'primevue/inputnumber';
import Button from 'primevue/button';
import { useToast } from 'primevue/usetoast';

const route = useRoute();
const router = useRouter();
const appStore = useAppStore();
const toast = useToast();
const { formatCurrency, formatDate, getProgress, getDaysRemaining } = useCampaign();

const campaign = ref(null);
const isLoading = ref(true);
const showBackingDialog = ref(false);
const selectedTier = ref(null);
const backingAmount = ref(10000);
const isProcessing = ref(false);
const backingsCount = ref(0);
const chartCanvas = ref(null);

const isCreator = computed(() => {
    return appStore.isAuthenticated && appStore.user?.id === campaign.value?.creator?.id;
});

function getYouTubeId(url) {
    if (!url) return null;
    const patterns = [
        /(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/,
        /youtube\.com\/shorts\/([a-zA-Z0-9_-]{11})/,
    ];
    for (const p of patterns) {
        const match = url.match(p);
        if (match) return match[1];
    }
    return null;
}

function selectTier(tier) {
    selectedTier.value = tier;
    if (tier.min_amount > backingAmount.value) {
        backingAmount.value = tier.min_amount;
    }
}

async function fetchCampaign() {
    isLoading.value = true;
    try {
        const response = await campaignApi.getById(route.params.id);
        campaign.value = response.data.data;
        if (response.data.data.backings_count) {
            backingsCount.value = response.data.data.backings_count;
        }
    } catch {
        campaign.value = null;
    } finally {
        isLoading.value = false;
    }
}

async function confirmBacking() {
    if (!backingAmount.value || backingAmount.value < 10000) return;

    isProcessing.value = true;
    try {
        const response = await backingApi.create({
            campaign_id: campaign.value.id,
            tier_id: selectedTier.value?.id || null,
            amount: backingAmount.value,
        });

        toast.add({
            severity: 'success',
            summary: 'Donasi Berhasil!',
            detail: `Anda telah mendonasikan ${formatCurrency(backingAmount.value)} untuk kampanye ini.`,
            life: 5000,
        });
        showBackingDialog.value = false;
        selectedTier.value = null;
        await fetchCampaign();
        await appStore.fetchUser();
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Gagal',
            detail: error.response?.data?.message || 'Terjadi kesalahan saat memproses donasi',
            life: 3000,
        });
    } finally {
        isProcessing.value = false;
    }
}

function drawChart() {
    if (!chartCanvas.value || !campaign.value) return;
    const canvas = chartCanvas.value;
    const ctx = canvas.getContext('2d');
    const dpr = window.devicePixelRatio || 1;
    const rect = canvas.getBoundingClientRect();
    canvas.width = rect.width * dpr;
    canvas.height = rect.height * dpr;
    ctx.scale(dpr, dpr);
    const w = rect.width;
    const h = rect.height;
    const padding = { top: 20, bottom: 30, left: 10, right: 10 };
    const chartW = w - padding.left - padding.right;
    const chartH = h - padding.top - padding.bottom;

    const collected = campaign.value.collected_amount || 0;
    const target = campaign.value.target_amount || 1;
    const progress = Math.min(collected / target, 1);

    ctx.clearRect(0, 0, w, h);

    ctx.strokeStyle = '#2A2A3E';
    ctx.lineWidth = 1;
    for (let i = 0; i <= 4; i++) {
        const y = padding.top + (chartH / 4) * i;
        ctx.beginPath();
        ctx.moveTo(padding.left, y);
        ctx.lineTo(w - padding.right, y);
        ctx.stroke();
        ctx.fillStyle = '#5A5A7A';
        ctx.font = '10px Inter, sans-serif';
        ctx.textAlign = 'right';
        ctx.fillText(`${(100 - i * 25)}%`, padding.left - 5, y + 3);
    }

    const barY = padding.top;
    const barH = chartH;
    const barW = chartW * 0.6;
    const barX = padding.left + chartW * 0.1;

    ctx.fillStyle = '#2A2A3E';
    ctx.beginPath();
    ctx.roundRect(barX, barY, barW, barH, 6);
    ctx.fill();

    const fillH = barH * progress;
    const fillY = barY + barH - fillH;
    const gradient = ctx.createLinearGradient(barX, fillY, barX, barY + barH);
    gradient.addColorStop(0, '#FF6363');
    gradient.addColorStop(1, '#FF8585');
    ctx.fillStyle = gradient;
    ctx.beginPath();
    ctx.roundRect(barX, fillY, barW, fillH, 6);
    ctx.fill();

    ctx.fillStyle = '#FFFFFF';
    ctx.font = 'bold 14px Inter, sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText(`${Math.round(progress * 100)}%`, barX + barW / 2, barY + barH / 2 + 5);

    ctx.fillStyle = '#888899';
    ctx.font = '11px Inter, sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText('Terkumpul', barX + barW / 2, h - 8);

    const milestones = [25, 50, 75, 100];
    milestones.forEach(m => {
        const mY = barY + barH - (barH * m / 100);
        ctx.fillStyle = progress * 100 >= m ? '#FF6363' : '#3A3A5A';
        ctx.beginPath();
        ctx.arc(barX + barW + 15, mY, 3, 0, Math.PI * 2);
        ctx.fill();
        ctx.fillStyle = '#5A5A7A';
        ctx.font = '9px Inter, sans-serif';
        ctx.textAlign = 'left';
        ctx.fillText(`${m}%`, barX + barW + 22, mY + 3);
    });
}

watch([campaign, chartCanvas], async () => {
    if (campaign.value && chartCanvas.value) {
        await nextTick();
        drawChart();
    }
}, { deep: true });

onMounted(async () => {
    await fetchCampaign();
    if (chartCanvas.value) {
        await nextTick();
        drawChart();
    }
});

let resizeHandler;
if (typeof window !== 'undefined') {
    resizeHandler = () => {
        if (chartCanvas.value) drawChart();
    };
    window.addEventListener('resize', resizeHandler);
}

onUnmounted(() => {
    if (resizeHandler) window.removeEventListener('resize', resizeHandler);
});
</script>
