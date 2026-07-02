<template>
    <div class="container-page">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Campaigns</h1>
                <p class="text-gray-600 mt-1">Jelajahi kampanye yang sedang berlangsung</p>
            </div>
            <router-link
                v-if="appStore.isAuthenticated"
                to="/campaigns/create"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors"
            >
                <i class="pi pi-plus mr-2"></i>
                New Campaign
            </router-link>
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap gap-3 mb-6">
            <input
                v-model="searchQuery"
                type="text"
                placeholder="Cari kampanye..."
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none w-full sm:w-64"
                @input="debouncedSearch"
            />
            <select
                v-model="filterCategory"
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                @change="fetchData"
            >
                <option value="">Semua Kategori</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                    {{ cat.name }}
                </option>
            </select>
            <select
                v-model="filterStatus"
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                @change="fetchData"
            >
                <option value="">Semua Status</option>
                <option value="active">Active</option>
                <option value="success">Success</option>
                <option value="failed">Failed</option>
            </select>
        </div>

        <!-- Loading -->
        <div v-if="isLoading" class="flex justify-center py-20">
            <i class="pi pi-spin pi-spinner text-4xl text-blue-600"></i>
        </div>

        <!-- Empty State -->
        <div v-else-if="campaigns.length === 0" class="text-center py-20">
            <i class="pi pi-inbox text-gray-300 text-6xl mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada kampanye</h3>
            <p class="text-gray-500">Belum ada kampanye yang sesuai dengan filter Anda.</p>
        </div>

        <!-- Campaign Grid -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
                v-for="campaign in campaigns"
                :key="campaign.id"
                class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-all group"
            >
                <router-link :to="`/campaigns/${campaign.id}`">
                    <div class="h-48 bg-gradient-to-br from-blue-400 to-blue-600 relative">
                        <div class="absolute inset-0 flex items-center justify-center text-white/30">
                            <i class="pi pi-image text-6xl"></i>
                        </div>
                        <div class="absolute top-3 right-3">
                            <span
                                :class="[
                                    'px-3 py-1 text-xs font-semibold rounded-full',
                                    campaign.status === 'active' ? 'bg-green-100 text-green-700' :
                                    campaign.status === 'success' ? 'bg-blue-100 text-blue-700' :
                                    'bg-gray-100 text-gray-700'
                                ]"
                            >
                                {{ campaign.status }}
                            </span>
                        </div>
                        <div class="absolute bottom-3 left-3">
                            <span class="px-3 py-1 bg-white/90 text-xs font-semibold rounded-full text-blue-700">
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
                    <p class="text-sm text-gray-500 mb-2 line-clamp-2">
                        {{ campaign.description }}
                    </p>
                    <p class="text-xs text-gray-400 mb-4">
                        oleh {{ campaign.creator?.name || 'Anonymous' }}
                    </p>
                    <div class="space-y-3">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div
                                class="bg-blue-600 h-2 rounded-full transition-all"
                                :style="{ width: `${getProgress(campaign.collected_amount, campaign.target_amount)}%` }"
                            ></div>
                        </div>
                        <div class="flex justify-between text-sm">
                            <div>
                                <p class="font-semibold text-gray-900">{{ formatCurrency(campaign.collected_amount) }}</p>
                                <p class="text-gray-500 text-xs">dari {{ formatCurrency(campaign.target_amount) }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900">{{ getDaysRemaining(campaign.deadline) }} hari</p>
                                <p class="text-gray-500 text-xs">tersisa</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="meta && meta.last_page > 1" class="flex justify-center mt-8">
            <div class="flex space-x-2">
                <button
                    v-for="page in meta.last_page"
                    :key="page"
                    @click="goToPage(page)"
                    :class="[
                        'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                        page === meta.current_page
                            ? 'bg-blue-600 text-white'
                            : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'
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
