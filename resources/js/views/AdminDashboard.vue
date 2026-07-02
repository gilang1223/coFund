<template>
    <div class="container-page animate-fade-in">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-md bg-brand-500/10 flex items-center justify-center">
                    <i class="pi pi-shield text-brand-400 text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-white">Admin Dashboard</h1>
                    <p class="text-gray-500 text-sm">Panel kontrol platform CoFund</p>
                </div>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="flex flex-wrap gap-1 mb-6 p-1 bg-navy-800/60 rounded-md border border-navy-700/50 w-fit">
            <button
                v-for="tab in tabs"
                :key="tab.key"
                @click="setActiveTab(tab.key)"
                :class="[
                    'px-4 py-2 rounded-md text-sm font-medium transition-all duration-200',
                    activeTab === tab.key
                        ? 'bg-brand-500/20 text-brand-400 shadow-sm'
                        : 'text-gray-500 hover:text-gray-300 hover:bg-navy-700/50'
                ]"
            >
                <i :class="[tab.icon, 'mr-1.5']"></i>
                {{ tab.label }}
                <span
                    v-if="tab.key === 'pending-reviews' && overviewStats?.campaigns?.pending_review > 0"
                    class="ml-1.5 px-1.5 py-0.5 text-xs rounded-full bg-orange-500/20 text-orange-400"
                >{{ overviewStats.campaigns.pending_review }}</span>
            </button>
        </div>

        <!-- ==================== OVERVIEW TAB ==================== -->
        <template v-if="activeTab === 'overview'">
            <div v-if="loading" class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div v-for="i in 4" :key="i" class="card p-5">
                        <div class="skeleton h-4 w-20 mb-3"></div>
                        <div class="skeleton h-8 w-16 mb-2"></div>
                        <div class="skeleton h-3 w-24"></div>
                    </div>
                </div>
                <div class="card p-6"><div class="skeleton h-48 w-full"></div></div>
            </div>

            <template v-else-if="overviewStats">
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                    <div class="card p-5 hover:border-navy-600 transition-colors">
                        <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Total Users</p>
                        <p class="text-3xl font-bold text-white">{{ overviewStats.users.total }}</p>
                        <p class="text-xs text-gray-600 mt-1">{{ overviewStats.users.creators }} creator · {{ overviewStats.users.backers }} backer</p>
                    </div>
                    <div class="card p-5 hover:border-navy-600 transition-colors">
                        <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Campaigns</p>
                        <p class="text-3xl font-bold text-white">{{ overviewStats.campaigns.total }}</p>
                        <p class="text-xs text-gray-600 mt-1">{{ overviewStats.campaigns.active }} active · {{ overviewStats.campaigns.pending_review }} pending</p>
                    </div>
                    <div class="card p-5 border-orange-500/20 hover:border-orange-500/30 transition-colors">
                        <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Pending Review</p>
                        <p class="text-3xl font-bold text-orange-400">{{ overviewStats.campaigns.pending_review }}</p>
                        <p class="text-xs text-gray-600 mt-1">Menunggu approval</p>
                    </div>
                    <div class="card p-5 hover:border-navy-600 transition-colors">
                        <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Platform Fees</p>
                        <p class="text-3xl font-bold text-green-400">{{ formatCurrency(overviewStats.financials.total_platform_fees) }}</p>
                        <p class="text-xs text-gray-600 mt-1">Total collected: {{ formatCurrency(overviewStats.financials.total_collected) }}</p>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card p-6 mb-8">
                    <h2 class="text-lg font-semibold text-white mb-4">Quick Actions</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        <button
                            @click="setActiveTab('pending-reviews')"
                            class="flex items-center gap-3 px-4 py-3 rounded-md bg-navy-800/50 hover:bg-navy-700/50 border border-navy-700 hover:border-orange-500/30 transition-all group"
                        >
                            <i class="pi pi-check-circle text-orange-400"></i>
                            <span class="text-sm text-gray-300 group-hover:text-white transition-colors">Pending Reviews ({{ overviewStats.campaigns.pending_review }})</span>
                        </button>
                        <button
                            @click="setActiveTab('campaigns')"
                            class="flex items-center gap-3 px-4 py-3 rounded-md bg-navy-800/50 hover:bg-navy-700/50 border border-navy-700 hover:border-brand-500/30 transition-all group"
                        >
                            <i class="pi pi-verified text-brand-400"></i>
                            <span class="text-sm text-gray-300 group-hover:text-white transition-colors">All Campaigns</span>
                        </button>
                        <button
                            @click="setActiveTab('users')"
                            class="flex items-center gap-3 px-4 py-3 rounded-md bg-navy-800/50 hover:bg-navy-700/50 border border-navy-700 hover:border-blue-400/30 transition-all group"
                        >
                            <i class="pi pi-users text-blue-400"></i>
                            <span class="text-sm text-gray-300 group-hover:text-white transition-colors">Manage Users</span>
                        </button>
                    </div>
                </div>

                <!-- Campaign Status Progress -->
                <div class="card p-6">
                    <h2 class="text-lg font-semibold text-white mb-4">Campaign Status Overview</h2>
                    <div class="space-y-3">
                        <div v-for="s in statuses" :key="s.key" class="group">
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-400 group-hover:text-gray-300 transition-colors">{{ s.label }}</span>
                                <span class="text-white font-medium">{{ s.value }}</span>
                            </div>
                            <div class="progress-bar h-2">
                                <div
                                    class="progress-fill h-2 transition-all duration-500"
                                    :class="s.color"
                                    :style="{ width: getPercent(s.value) + '%' }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <div v-else-if="error" class="text-center py-10">
                <div class="w-16 h-16 rounded-md bg-orange-500/10 flex items-center justify-center mx-auto mb-4">
                    <i class="pi pi-exclamation-triangle text-orange-400 text-2xl"></i>
                </div>
                <p class="text-orange-400">{{ error }}</p>
                <button @click="fetchOverview" class="mt-4 text-sm text-brand-400 hover:text-brand-300 transition-colors">
                    <i class="pi pi-refresh mr-1"></i> Coba Lagi
                </button>
            </div>
        </template>

        <!-- ==================== PENDING REVIEWS TAB ==================== -->
        <template v-if="activeTab === 'pending-reviews'">
            <PendingReviewsSection
                :loading="pendingLoading"
                :campaigns="pendingCampaigns"
                :meta="pendingMeta"
                :error="pendingError"
                @fetch="fetchPendingReviews"
                @refresh="() => { pendingPage = 1; fetchPendingReviews(); }"
            />
        </template>

        <!-- ==================== ALL CAMPAIGNS TAB ==================== -->
        <template v-if="activeTab === 'campaigns'">
            <AllCampaignsSection
                :loading="campaignsLoading"
                :campaigns="allCampaigns"
                :meta="campaignsMeta"
                :error="campaignsError"
                @fetch="fetchAllCampaigns"
                @refresh="() => { campaignsPage = 1; fetchAllCampaigns(); }"
            />
        </template>

        <!-- ==================== USERS TAB ==================== -->
        <template v-if="activeTab === 'users'">
            <UsersSection
                :loading="usersLoading"
                :users="usersList"
                :meta="usersMeta"
                :error="usersError"
                @fetch="fetchUsers"
                @refresh="() => { usersPage = 1; fetchUsers(); }"
            />
        </template>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { adminApi } from '@/services/api';
import { useCampaign } from '@/composables/useCampaign';
import PendingReviewsSection from '@/views/admin/PendingReviewsSection.vue';
import AllCampaignsSection from '@/views/admin/AllCampaignsSection.vue';
import UsersSection from '@/views/admin/UsersSection.vue';

const { formatCurrency } = useCampaign();

const tabs = [
    { key: 'overview', label: 'Overview', icon: 'pi pi-chart-bar' },
    { key: 'pending-reviews', label: 'Pending Reviews', icon: 'pi pi-check-circle' },
    { key: 'campaigns', label: 'All Campaigns', icon: 'pi pi-verified' },
    { key: 'users', label: 'Users', icon: 'pi pi-users' },
];
const activeTab = ref('overview');

function setActiveTab(key) {
    activeTab.value = key;
    if (key === 'pending-reviews' && pendingCampaigns.value === null) fetchPendingReviews();
    if (key === 'campaigns' && allCampaigns.value === null) fetchAllCampaigns();
    if (key === 'users' && usersList.value === null) fetchUsers();
}

// --- Overview State ---
const overviewStats = ref(null);
const loading = ref(true);
const error = ref(null);

const statuses = [
    { key: 'active', label: 'Active', value: 0, color: 'bg-green-500' },
    { key: 'pending_review', label: 'Pending Review', value: 0, color: 'bg-orange-500' },
    { key: 'success', label: 'Success', value: 0, color: 'bg-brand-500' },
    { key: 'failed', label: 'Failed', value: 0, color: 'bg-red-500/60' },
];

function getPercent(value) {
    if (!overviewStats.value?.campaigns?.total) return 0;
    return (value / overviewStats.value.campaigns.total) * 100;
}

async function fetchOverview() {
    loading.value = true;
    error.value = null;
    try {
        const res = await adminApi.getOverview();
        overviewStats.value = res.data.data;
        statuses[0].value = res.data.data.campaigns.active;
        statuses[1].value = res.data.data.campaigns.pending_review;
        statuses[2].value = res.data.data.campaigns.success;
        statuses[3].value = res.data.data.campaigns.failed;
    } catch (err) {
        error.value = err.response?.data?.message || 'Gagal memuat data admin';
    } finally {
        loading.value = false;
    }
}

// --- Pending Reviews State ---
const pendingCampaigns = ref(null);
const pendingLoading = ref(false);
const pendingError = ref(null);
const pendingMeta = ref(null);
let pendingPage = 1;

async function fetchPendingReviews(page = 1) {
    pendingPage = page;
    pendingLoading.value = true;
    pendingError.value = null;
    try {
        const res = await adminApi.getPendingReviews({ page: pendingPage });
        pendingCampaigns.value = res.data.data;
        pendingMeta.value = res.data.meta;
    } catch (err) {
        pendingError.value = err.response?.data?.message || 'Gagal memuat pending reviews';
    } finally {
        pendingLoading.value = false;
    }
}

// --- All Campaigns State ---
const allCampaigns = ref(null);
const campaignsLoading = ref(false);
const campaignsError = ref(null);
const campaignsMeta = ref(null);
let campaignsPage = 1;

async function fetchAllCampaigns(page = 1) {
    campaignsPage = page;
    campaignsLoading.value = true;
    campaignsError.value = null;
    try {
        const res = await adminApi.getAllCampaigns({ page: campaignsPage });
        allCampaigns.value = res.data.data;
        campaignsMeta.value = res.data.meta;
    } catch (err) {
        campaignsError.value = err.response?.data?.message || 'Gagal memuat campaigns';
    } finally {
        campaignsLoading.value = false;
    }
}

// --- Users State ---
const usersList = ref(null);
const usersLoading = ref(false);
const usersError = ref(null);
const usersMeta = ref(null);
let usersPage = 1;

async function fetchUsers(page = 1) {
    usersPage = page;
    usersLoading.value = true;
    usersError.value = null;
    try {
        const res = await adminApi.getUsers({ page: usersPage });
        usersList.value = res.data.data;
        usersMeta.value = res.data.meta;
    } catch (err) {
        usersError.value = err.response?.data?.message || 'Gagal memuat users';
    } finally {
        usersLoading.value = false;
    }
}

onMounted(fetchOverview);
</script>
