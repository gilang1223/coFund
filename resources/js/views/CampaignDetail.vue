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
                    <!-- Image -->
                    <div class="h-64 md:h-96 bg-gradient-to-br from-brand-500/20 to-navy-800 rounded-lg flex items-center justify-center text-navy-600">
                        <i class="pi pi-image text-8xl"></i>
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
                        <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">{{ campaign.title }}</h1>
                        <p class="text-gray-500">
                            oleh <span class="font-medium text-gray-300">{{ campaign.creator?.name || 'Anonymous' }}</span>
                            &middot; Dibuat {{ formatDate(campaign.created_at) }}
                        </p>
                    </div>

                    <!-- Description -->
                    <div class="card p-6">
                        <h2 class="text-lg font-semibold text-white mb-4">Tentang Kampanye</h2>
                        <p class="text-gray-400 leading-relaxed whitespace-pre-line">{{ campaign.description }}</p>
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
                            v-if="campaign.status === 'active'"
                            @click="showBackingDialog = true"
                            class="w-full py-3 bg-brand-500 text-white font-semibold rounded-md hover:bg-brand-600 transition-all duration-200"
                        >
                            <i class="pi pi-heart mr-2"></i>
                            Dukung Kampanye Ini
                        </button>

                        <!-- Tiers -->
                        <div v-if="campaign.tiers?.length" class="mt-6 space-y-3">
                            <h3 class="font-semibold text-gray-200">Pilih Reward</h3>
                            <div
                                v-for="tier in campaign.tiers"
                                :key="tier.id"
                                @click="selectedTier = tier"
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
                    label="Konfirmasi Donasi"
                    icon="pi pi-check"
                    class="w-full"
                    @click="confirmBacking"
                    :loading="isProcessing"
                />
            </div>
        </Dialog>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useAppStore } from '@/stores/app';
import { useCampaign } from '@/composables/useCampaign';
import { campaignApi, backingApi } from '@/services/api';
import Dialog from 'primevue/dialog';
import InputNumber from 'primevue/inputnumber';
import Button from 'primevue/button';
import { useToast } from 'primevue/usetoast';

const route = useRoute();
const appStore = useAppStore();
const toast = useToast();
const { formatCurrency, formatDate, getProgress, getDaysRemaining } = useCampaign();

const campaign = ref(null);
const isLoading = ref(true);
const showBackingDialog = ref(false);
const selectedTier = ref(null);
const backingAmount = ref(10000);
const isProcessing = ref(false);

async function fetchCampaign() {
    isLoading.value = true;
    try {
        const response = await campaignApi.getById(route.params.id);
        campaign.value = response.data.data;
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
        await backingApi.create({
            campaign_id: campaign.value.id,
            tier_id: selectedTier.value?.id || null,
            amount: backingAmount.value,
        });
        toast.add({
            severity: 'success',
            summary: 'Sukses',
            detail: 'Donasi berhasil dibuat!',
            life: 3000,
        });
        showBackingDialog.value = false;
        await fetchCampaign();
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Gagal',
            detail: error.response?.data?.message || 'Donasi gagal',
            life: 3000,
        });
    } finally {
        isProcessing.value = false;
    }
}

onMounted(fetchCampaign);
</script>
