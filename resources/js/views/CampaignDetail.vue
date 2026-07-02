<template>
    <div class="container-page">
        <div v-if="isLoading" class="flex justify-center py-20">
            <i class="pi pi-spin pi-spinner text-4xl text-blue-600"></i>
        </div>

        <template v-else-if="campaign">
            <!-- Breadcrumb -->
            <nav class="text-sm text-gray-500 mb-6">
                <router-link to="/campaigns" class="hover:text-blue-600">Campaigns</router-link>
                <span class="mx-2">/</span>
                <span class="text-gray-900">{{ campaign.title }}</span>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Image -->
                    <div class="h-64 md:h-96 bg-gradient-to-br from-blue-400 to-blue-600 rounded-2xl flex items-center justify-center text-white/30">
                        <i class="pi pi-image text-8xl"></i>
                    </div>

                    <!-- Title & Meta -->
                    <div>
                        <div class="flex flex-wrap items-center gap-3 mb-3">
                            <span
                                :class="[
                                    'px-3 py-1 text-xs font-semibold rounded-full',
                                    campaign.status === 'active' ? 'bg-green-100 text-green-700' :
                                    campaign.status === 'success' ? 'bg-blue-100 text-blue-700' :
                                    campaign.status === 'draft' ? 'bg-yellow-100 text-yellow-700' :
                                    'bg-gray-100 text-gray-700'
                                ]"
                            >
                                {{ campaign.status }}
                            </span>
                            <span class="px-3 py-1 bg-blue-100 text-xs font-semibold rounded-full text-blue-700">
                                {{ campaign.category?.name }}
                            </span>
                        </div>
                        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">{{ campaign.title }}</h1>
                        <p class="text-gray-500">
                            oleh <span class="font-medium text-gray-700">{{ campaign.creator?.name || 'Anonymous' }}</span>
                            &middot; Dibuat {{ formatDate(campaign.created_at) }}
                        </p>
                    </div>

                    <!-- Description -->
                    <div class="bg-white rounded-xl p-6 border border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Tentang Kampanye</h2>
                        <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ campaign.description }}</p>
                    </div>

                    <!-- Updates -->
                    <div v-if="campaign.updates?.length" class="bg-white rounded-xl p-6 border border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Pembaruan</h2>
                        <div v-for="update in campaign.updates" :key="update.id" class="border-l-2 border-blue-200 pl-4 py-3 mb-4">
                            <h3 class="font-medium text-gray-900">{{ update.title }}</h3>
                            <p class="text-sm text-gray-600 mt-1">{{ update.content }}</p>
                            <p class="text-xs text-gray-400 mt-2">{{ formatDate(update.created_at) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Funding Card -->
                    <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm sticky top-24">
                        <div class="text-center mb-6">
                            <p class="text-3xl font-bold text-gray-900">{{ formatCurrency(campaign.collected_amount) }}</p>
                            <p class="text-gray-500 text-sm">terkumpul dari {{ formatCurrency(campaign.target_amount) }}</p>
                        </div>

                        <div class="w-full bg-gray-200 rounded-full h-3 mb-4">
                            <div
                                class="bg-blue-600 h-3 rounded-full"
                                :style="{ width: `${getProgress(campaign.collected_amount, campaign.target_amount)}%` }"
                            ></div>
                        </div>

                        <div class="flex justify-between text-sm mb-6">
                            <div>
                                <p class="font-semibold text-gray-900">{{ getProgress(campaign.collected_amount, campaign.target_amount) }}%</p>
                                <p class="text-gray-500">tercapai</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900">{{ getDaysRemaining(campaign.deadline) }} hari</p>
                                <p class="text-gray-500">tersisa</p>
                            </div>
                        </div>

                        <button
                            v-if="campaign.status === 'active'"
                            @click="showBackingDialog = true"
                            class="w-full py-3 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition-colors"
                        >
                            <i class="pi pi-heart mr-2"></i>
                            Dukung Kampanye Ini
                        </button>

                        <!-- Tiers -->
                        <div v-if="campaign.tiers?.length" class="mt-6 space-y-3">
                            <h3 class="font-semibold text-gray-900">Pilih Reward</h3>
                            <div
                                v-for="tier in campaign.tiers"
                                :key="tier.id"
                                @click="selectedTier = tier"
                                :class="[
                                    'p-4 rounded-xl border-2 cursor-pointer transition-all',
                                    selectedTier?.id === tier.id
                                        ? 'border-blue-500 bg-blue-50'
                                        : 'border-gray-200 hover:border-blue-300'
                                ]"
                            >
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-semibold text-gray-900">{{ tier.name }}</h4>
                                        <p class="text-sm text-gray-600 mt-1">{{ tier.reward_description }}</p>
                                    </div>
                                    <span class="text-blue-600 font-bold">{{ formatCurrency(tier.min_amount) }}</span>
                                </div>
                                <p class="text-xs text-gray-400 mt-2">
                                    {{ tier.remaining_quota }} dari {{ tier.quota }} tersisa
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <div v-else class="text-center py-20">
            <i class="pi pi-exclamation-circle text-gray-300 text-6xl mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900">Kampanye tidak ditemukan</h3>
        </div>

        <!-- Backing Dialog -->
        <Dialog
            v-model:visible="showBackingDialog"
            header="Dukung Kampanye"
            :modal="true"
            class="w-full max-w-md"
        >
            <div v-if="!appStore.isAuthenticated" class="text-center py-4">
                <p class="text-gray-600 mb-4">Silakan login atau daftar untuk mendukung kampanye</p>
                <router-link to="/login" class="text-blue-600 font-medium hover:underline">
                    Login / Register
                </router-link>
            </div>
            <div v-else class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Donasi</label>
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
                <div v-if="selectedTier" class="bg-blue-50 rounded-lg p-3">
                    <p class="text-sm font-medium text-blue-900">Reward: {{ selectedTier.name }}</p>
                    <p class="text-xs text-blue-700 mt-1">Min. {{ formatCurrency(selectedTier.min_amount) }}</p>
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
