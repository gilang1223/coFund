<template>
    <div class="animate-fade-in">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-semibold text-white">Pending Reviews</h2>
                <p class="text-gray-500 text-sm mt-0.5">Kampanye yang menunggu persetujuan admin</p>
            </div>
            <button
                @click="$emit('refresh')"
                class="text-sm text-brand-400 hover:text-brand-300 transition-colors flex items-center gap-1"
            >
                <i class="pi pi-refresh"></i> Refresh
            </button>
        </div>

        <!-- Skeleton -->
        <div v-if="loading" class="space-y-3">
            <div v-for="i in 3" :key="i" class="card p-5">
                <div class="flex items-start gap-4">
                    <div class="skeleton h-16 w-16 rounded-md shrink-0"></div>
                    <div class="flex-1 space-y-2">
                        <div class="skeleton h-4 w-48"></div>
                        <div class="skeleton h-3 w-32"></div>
                        <div class="skeleton h-3 w-64"></div>
                    </div>
                    <div class="flex gap-2">
                        <div class="skeleton h-8 w-20 rounded-md"></div>
                        <div class="skeleton h-8 w-20 rounded-md"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Error -->
        <div v-else-if="error" class="text-center py-10">
            <div class="w-14 h-14 rounded-md bg-orange-500/10 flex items-center justify-center mx-auto mb-3">
                <i class="pi pi-exclamation-triangle text-orange-400 text-xl"></i>
            </div>
            <p class="text-orange-400 mb-3">{{ error }}</p>
            <button @click="$emit('refresh')" class="text-sm text-brand-400 hover:text-brand-300 transition-colors underline">Coba Lagi</button>
        </div>

        <!-- Empty -->
        <div v-else-if="!campaigns || campaigns.length === 0" class="card p-10 text-center">
            <div class="w-16 h-16 rounded-md bg-green-500/10 flex items-center justify-center mx-auto mb-4">
                <i class="pi pi-check-circle text-green-400 text-2xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-300 mb-1">Semua sudah direview!</h3>
            <p class="text-gray-500 text-sm">Tidak ada kampanye yang menunggu persetujuan.</p>
        </div>

        <!-- Pending Campaigns List -->
        <div v-else class="space-y-3">
            <div
                v-for="campaign in campaigns"
                :key="campaign.id"
                class="card p-5 hover:border-navy-600 transition-all"
            >
                <div class="flex items-start gap-4">
                    <div class="w-16 h-16 rounded-md bg-gradient-to-br from-brand-500/20 to-navy-800 flex items-center justify-center shrink-0">
                        <i class="pi pi-image text-navy-600 text-xl"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="font-semibold text-white truncate">{{ campaign.title }}</h3>
                            <span
                                :class="[
                                    campaign.status === 'draft' ? 'badge-draft text-xs' :
                                    campaign.status === 'review' ? 'badge-default text-xs' :
                                    'badge-failed text-xs'
                                ]">
                                {{ campaign.status }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 mb-1">
                            oleh <span class="text-gray-400">{{ campaign.creator?.name || 'Unknown' }}</span>
                            · {{ campaign.category?.name || 'General' }}
                        </p>
                        <p class="text-sm text-gray-400 line-clamp-2">{{ campaign.description }}</p>
                        <div class="flex items-center gap-4 mt-2 text-xs text-gray-600">
                            <span>Target: {{ formatCurrency(campaign.target_amount) }}</span>
                            <span>Dibuat: {{ formatDate(campaign.created_at) }}</span>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2 shrink-0">
                        <button
                            @click="handleAction(campaign.id, 'approve')"
                            :disabled="actionLoading === campaign.id"
                            class="px-4 py-1.5 bg-green-500/10 text-green-400 border border-green-500/20 rounded-md text-sm font-medium hover:bg-green-500/20 transition-all disabled:opacity-50"
                        >
                            <i v-if="actionLoading === campaign.id" class="pi pi-spin pi-spinner mr-1"></i>
                            <i v-else class="pi pi-check mr-1"></i>
                            Approve
                        </button>
                        <button
                            @click="handleAction(campaign.id, 'reject')"
                            :disabled="actionLoading === campaign.id"
                            class="px-4 py-1.5 bg-orange-500/10 text-orange-400 border border-orange-500/20 rounded-md text-sm font-medium hover:bg-orange-500/20 transition-all disabled:opacity-50"
                        >
                            <i v-if="actionLoading === campaign.id" class="pi pi-spin pi-spinner mr-1"></i>
                            <i v-else class="pi pi-times mr-1"></i>
                            Reject
                        </button>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="meta && meta.last_page > 1" class="flex justify-center pt-4">
                <div class="flex gap-2">
                    <button
                        v-for="p in meta.last_page"
                        :key="p"
                        @click="goToPage(p)"
                        :class="[
                            'px-3 py-1.5 rounded-md text-sm font-medium transition-all',
                            p === meta.current_page
                                ? 'bg-brand-500/20 text-brand-400'
                                : 'bg-navy-800 text-gray-500 hover:text-gray-300 border border-navy-700'
                        ]"
                    >{{ p }}</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { campaignApi } from '@/services/api';
import { useCampaign } from '@/composables/useCampaign';
import dayjs from '@/plugins/dayjs';

const props = defineProps({
    loading: Boolean,
    campaigns: Array,
    meta: Object,
    error: String,
});

const emit = defineEmits(['fetch', 'refresh']);

const { formatCurrency } = useCampaign();
const actionLoading = ref(null);

function formatDate(date) {
    if (!date) return '-';
    return dayjs(date).format('D MMM YYYY');
}

function goToPage(page) {
    emit('fetch', page);
}

async function handleAction(id, action) {
    actionLoading.value = id;
    try {
        if (action === 'approve') {
            await campaignApi.approve(id);
        } else {
            await campaignApi.reject(id);
        }
        emit('refresh');
    } catch {
        // error handled silently
    } finally {
        actionLoading.value = null;
    }
}
</script>
