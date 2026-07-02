<template>
    <div class="container-page animate-fade-in">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-white">Campaigns</h1>
                <p class="text-gray-500 mt-1">Jelajahi kampanye yang sedang berlangsung</p>
            </div>
            <router-link
                v-if="appStore.isAuthenticated"
                to="/campaigns/create"
                class="inline-flex items-center px-4 py-2 bg-brand-500 text-white font-medium rounded-md hover:bg-brand-600 transition-all duration-200"
            >
                <i class="pi pi-plus mr-2 text-xs"></i>
                New Campaign
            </router-link>
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap gap-3 mb-6">
            <div class="relative flex-1 min-w-[200px] max-w-sm">
                <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm"></i>
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Cari kampanye..."
                    class="input-field pl-10"
                    @input="debouncedSearch"
                />
            </div>
            <select
                v-model="filterCategory"
                class="input-field w-auto min-w-[160px]"
                @change="fetchData"
            >
                <option value="">Semua Kategori</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                    {{ cat.name }}
                </option>
            </select>
            <select
                v-model="filterStatus"
                class="input-field w-auto min-w-[140px]"
                @change="fetchData"
            >
                <option value="">Semua Status</option>
                <option value="active">Active</option>
                <option value="success">Success</option>
                <option value="failed">Failed</option>
            </select>
        </div>

        <!-- Skeleton Loading -->
        <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <div v-for="i in 6" :key="i" class="card overflow-hidden">
                <div class="skeleton h-48 rounded-none"></div>
                <div class="p-5 space-y-3">
                    <div class="skeleton-title"></div>
                    <div class="skeleton-text"></div>
                    <div class="skeleton-text w-2/3"></div>
                    <div class="skeleton h-2 w-full mt-3"></div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else-if="campaigns.length === 0" class="text-center py-20">
            <div class="w-16 h-16 rounded-md bg-navy-800 flex items-center justify-center mx-auto mb-4">
                <i class="pi pi-inbox text-gray-600 text-2xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-300 mb-2">Tidak ada kampanye</h3>
            <p class="text-gray-500">Belum ada kampanye yang sesuai dengan filter Anda.</p>
        </div>

        <!-- Campaign Grid -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <div
                v-for="campaign in campaigns"
                :key="campaign.id"
                class="card-hover overflow-hidden group"
            >
                <router-link :to="`/campaigns/${campaign.id}`">
                    <div class="h-48 bg-gradient-to-br from-brand-500/20 to-navy-800 relative">
                        <div class="absolute inset-0 flex items-center justify-center text-navy-600">
                            <i class="pi pi-image text-6xl"></i>
                        </div>
                        <div class="absolute inset-0 bg-gradient-to-t from-navy-800 via-transparent to-transparent"></div>
                        <div class="absolute top-3 right-3">
                            <span
                                :class="[
                                    campaign.status === 'active' ? 'badge-active' :
                                    campaign.status === 'success' ? 'badge-success' :
                                    campaign.status === 'draft' ? 'badge-draft' :
                                    'badge-default'
                                ]"
                            >
                                {{ campaign.status }}
                            </span>
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
                        <h3 class="font-semibold text-white mb-2 group-hover:text-brand-400 transition-colors line-clamp-2 text-sm">
                            {{ campaign.title }}
                        </h3>
                    </router-link>
                    <p class="text-sm text-gray-500 mb-2 line-clamp-2">
                        {{ campaign.description }}
                    </p>
                    <p class="text-xs text-gray-600 mb-4">
                        oleh {{ campaign.creator?.name || 'Anonymous' }}
                    </p>
                    <div class="space-y-2.5">
                        <div class="progress-bar h-1.5">
                            <div
                                class="progress-fill h-1.5"
                                :style="{ width: `${getProgress(campaign.collected_amount, campaign.target_amount)}%` }"
                            ></div>
                        </div>
                        <div class="flex justify-between text-xs">
                            <div>
                                <p class="font-semibold text-gray-200">{{ formatCurrency(campaign.collected_amount) }}</p>
                                <p class="text-gray-600">dari {{ formatCurrency(campaign.target_amount) }}</p>
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

        <!-- Pagination -->
        <div v-if="meta && meta.last_page > 1" class="flex justify-center mt-10">
            <div class="flex space-x-2">
                <button
                    v-for="page in meta.last_page"
                    :key="page"
                    @click="goToPage(page)"
                    :class="[
                        'px-3.5 py-2 rounded-md text-sm font-medium transition-all duration-200',
                        page === meta.current_page
                            ? 'bg-brand-500 text-white'
                            : 'bg-navy-800 text-gray-400 hover:text-white hover:bg-navy-700 border border-navy-700'
                    ]"
                >
                    {{ page }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAppStore } from '@/stores/app';
import { useCampaign } from '@/composables/useCampaign';
import { categoryApi } from '@/services/api';

const router = useRouter();
const appStore = useAppStore();
const {
    campaigns,
    isLoading,
    meta,
    fetchCampaigns,
    formatCurrency,
    getProgress,
    getDaysRemaining,
} = useCampaign();

const categories = ref([]);
const searchQuery = ref('');
const filterCategory = ref('');
const filterStatus = ref('');
let debounceTimer = null;

async function fetchData() {
    const params = {
        page: meta.value?.current_page || 1,
    };
    if (filterCategory.value) params.category_id = filterCategory.value;
    if (filterStatus.value) params.status = filterStatus.value;
    if (searchQuery.value) params.search = searchQuery.value;

    await fetchCampaigns(params);
}

function debouncedSearch() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        fetchData();
    }, 500);
}

function goToPage(page) {
    fetchCampaigns({ page, ...(filterCategory.value ? { category_id: filterCategory.value } : {}) });
}

onMounted(async () => {
    try {
        const response = await categoryApi.getAll();
        categories.value = response.data.data;
    } catch {
        // ignore
    }
    await fetchData();
});
</script>
