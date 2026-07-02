<template>
    <div class="container-page animate-fade-in">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-8 gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-white">Campaigns</h1>
                <p class="text-gray-500 mt-1">Jelajahi kampanye yang sedang berlangsung</p>
            </div>
            <router-link
                v-if="appStore.isAuthenticated"
                to="/campaigns/create"
                class="inline-flex items-center px-4 py-2 bg-brand-500 text-white font-medium rounded-md hover:bg-brand-600 transition-all duration-200 shadow-lg shadow-brand-500/20 hover:shadow-brand-500/30"
            >
                <i class="pi pi-plus mr-2 text-xs"></i>
                New Campaign
            </router-link>
        </div>

        <!-- Search & Filters -->
        <div class="flex flex-wrap gap-3 mb-4">
            <div class="relative flex-1 min-w-[200px] max-w-sm">
                <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm"></i>
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Cari kampanye..."
                    class="input-field pl-10 transition-all duration-200 focus:border-brand-500/50"
                    @input="debouncedSearch"
                />
            </div>
            <select
                v-model="filterCategory"
                class="input-field w-auto min-w-[160px] transition-all duration-200 focus:border-brand-500/50"
                @change="fetchData"
            >
                <option value="">Semua Kategori</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                    {{ cat.name }}
                </option>
            </select>
            <select
                v-model="filterStatus"
                class="input-field w-auto min-w-[140px] transition-all duration-200 focus:border-brand-500/50"
                @change="fetchData"
            >
                <option value="">Semua Status</option>
                <option value="active">Active</option>
                <option value="success">Success</option>
                <option value="failed">Failed</option>
            </select>
        </div>

        <!-- Active Filter Tags -->
        <div v-if="hasActiveFilters" class="flex flex-wrap gap-2 mb-5">
            <span
                v-if="searchQuery"
                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-brand-500/10 text-brand-400 text-xs border border-brand-500/20"
            >
                <i class="pi pi-search"></i> "{{ searchQuery }}"
                <button @click="searchQuery = ''; debouncedSearch()" class="hover:text-white transition-colors">
                    <i class="pi pi-times text-xs"></i>
                </button>
            </span>
            <span
                v-if="filterCategory"
                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-blue-500/10 text-blue-400 text-xs border border-blue-500/20"
            >
                <i class="pi pi-tag"></i> {{ getCategoryName(filterCategory) }}
                <button @click="filterCategory = ''; fetchData()" class="hover:text-white transition-colors">
                    <i class="pi pi-times text-xs"></i>
                </button>
            </span>
            <span
                v-if="filterStatus"
                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-green-500/10 text-green-400 text-xs border border-green-500/20"
            >
                <i class="pi pi-filter"></i> {{ filterStatus }}
                <button @click="filterStatus = ''; fetchData()" class="hover:text-white transition-colors">
                    <i class="pi pi-times text-xs"></i>
                </button>
            </span>
            <button
                @click="clearAllFilters"
                class="text-xs text-gray-500 hover:text-gray-300 transition-colors underline underline-offset-2"
            >
                Clear all
            </button>
        </div>

        <!-- Transition wrapper for loading/content/empty -->
        <div class="relative">
            <!-- Skeleton Loading -->
            <Transition name="fade">
                <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div v-for="i in 6" :key="i" class="card overflow-hidden" :style="{ animationDelay: `${i * 80}ms` }">
                        <div class="skeleton h-48 rounded-none"></div>
                        <div class="p-5 space-y-3">
                            <div class="skeleton-title"></div>
                            <div class="skeleton-text"></div>
                            <div class="skeleton-text w-2/3"></div>
                            <div class="skeleton h-2 w-full mt-3"></div>
                        </div>
                    </div>
                </div>
            </Transition>

            <!-- Empty State -->
            <Transition name="fade">
                <div v-if="!isLoading && campaigns.length === 0" class="text-center py-20">
                    <div class="w-16 h-16 rounded-md bg-navy-800 flex items-center justify-center mx-auto mb-4">
                        <i class="pi pi-inbox text-gray-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-300 mb-2">Tidak ada kampanye</h3>
                    <p class="text-gray-500">Belum ada kampanye yang sesuai dengan filter Anda.</p>
                    <button v-if="hasActiveFilters" @click="clearAllFilters" class="mt-4 text-sm text-brand-400 hover:text-brand-300 transition-colors">
                        <i class="pi pi-refresh mr-1"></i> Reset Filter
                    </button>
                </div>
            </Transition>

            <!-- Campaign Grid -->
            <TransitionGroup
                v-if="!isLoading && campaigns.length > 0"
                name="campaign-card"
                tag="div"
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5"
            >
                <div
                    v-for="(campaign, index) in campaigns"
                    :key="campaign.id"
                    class="card-hover overflow-hidden group"
                    :style="{ animationDelay: `${index * 60}ms` }"
                >
                    <router-link :to="`/campaigns/${campaign.id}`">
                        <div class="h-48 bg-gradient-to-br from-brand-500/20 to-navy-800 relative overflow-hidden">
                            <div class="absolute inset-0 flex items-center justify-center text-navy-600 group-hover:scale-110 transition-transform duration-500">
                                <i class="pi pi-image text-6xl"></i>
                            </div>
                            <div class="absolute inset-0 bg-gradient-to-t from-navy-800 via-transparent to-transparent"></div>
                            <div class="absolute top-3 right-3">
                                <span
                                    :class="[
                                        'transition-all duration-200',
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
                            oleh <span class="text-gray-400">{{ campaign.creator?.name || 'Anonymous' }}</span>
                        </p>
                        <div class="space-y-2.5">
                            <div class="progress-bar h-1.5">
                                <div
                                    class="progress-fill h-1.5 transition-all duration-700 ease-out"
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
            </TransitionGroup>
        </div>

        <!-- Pagination -->
        <Transition name="fade">
            <div v-if="meta && meta.last_page > 1" class="flex justify-center mt-10">
                <div class="flex gap-1.5">
                    <button
                        v-for="page in meta.last_page"
                        :key="page"
                        @click="goToPage(page)"
                        :class="[
                            'px-3.5 py-2 rounded-md text-sm font-medium transition-all duration-200',
                            page === meta.current_page
                                ? 'bg-brand-500 text-white shadow-lg shadow-brand-500/20'
                                : 'bg-navy-800 text-gray-400 hover:text-white hover:bg-navy-700 border border-navy-700'
                        ]"
                    >
                        {{ page }}
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAppStore } from '@/stores/app';
import { useCampaign } from '@/composables/useCampaign';
import { categoryApi } from '@/services/api';

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

const hasActiveFilters = computed(() =>
    searchQuery.value || filterCategory.value || filterStatus.value
);

function getCategoryName(id) {
    const cat = categories.value.find(c => c.id == id);
    return cat?.name || id;
}

async function fetchData() {
    const params = { page: meta.value?.current_page || 1 };
    if (filterCategory.value) params.category_id = filterCategory.value;
    if (filterStatus.value) params.status = filterStatus.value;
    if (searchQuery.value) params.search = searchQuery.value;
    await fetchCampaigns(params);
}

function debouncedSearch() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => fetchData(), 400);
}

function clearAllFilters() {
    searchQuery.value = '';
    filterCategory.value = '';
    filterStatus.value = '';
    fetchData();
}

function goToPage(page) {
    const params = { page };
    if (filterCategory.value) params.category_id = filterCategory.value;
    if (filterStatus.value) params.status = filterStatus.value;
    if (searchQuery.value) params.search = searchQuery.value;
    fetchCampaigns(params);
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
