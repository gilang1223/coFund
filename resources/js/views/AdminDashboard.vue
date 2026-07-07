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
                <span
                    v-if="tab.key === 'creator-requests' && pendingCreatorRequestsCount > 0"
                    class="ml-1.5 px-1.5 py-0.5 text-xs rounded-full bg-purple-500/20 text-purple-400"
                >{{ pendingCreatorRequestsCount }}</span>
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
                        <p class="text-xs text-gray-600 mt-1">
                            {{ overviewStats.campaigns.active }} active ·
                            {{ overviewStats.campaigns.success }} success ·
                            {{ overviewStats.campaigns.rejected }} rejected ·
                            {{ overviewStats.campaigns.failed }} failed
                        </p>
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

        <!-- ==================== SUPPORT MESSAGES TAB ==================== -->
        <template v-if="activeTab === 'support'">
            <div class="space-y-4">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-white">Pesan Dukungan</h2>
                    <button @click="fetchConversations" class="text-sm text-brand-400 hover:text-brand-300 transition-colors">
                        <i class="pi pi-refresh mr-1"></i> Refresh
                    </button>
                </div>

                <div v-if="supportLoading" class="space-y-2">
                    <div v-for="i in 3" :key="i" class="card p-4">
                        <div class="skeleton h-4 w-40 mb-2"></div>
                        <div class="skeleton h-3 w-64"></div>
                    </div>
                </div>

                <div v-else-if="conversations.length === 0" class="card p-10 text-center">
                    <div class="w-16 h-16 rounded-md bg-navy-800 flex items-center justify-center mx-auto mb-4">
                        <i class="pi pi-inbox text-gray-600 text-2xl"></i>
                    </div>
                    <p class="text-gray-500">Belum ada pesan dari user.</p>
                </div>

                <!-- Conversations List -->
                <div v-else class="space-y-2">
                    <div
                        v-for="conv in conversations"
                        :key="conv.user_id"
                        @click="openConversation(conv)"
                        class="card p-4 hover:border-navy-600 transition-all cursor-pointer"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-10 h-10 rounded-full bg-brand-500/20 flex items-center justify-center text-sm font-bold text-brand-400 shrink-0">
                                    {{ getInitials(conv.user?.name) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-medium text-white truncate">{{ conv.user?.name }}</p>
                                        <span class="text-[11px] text-gray-500 hidden sm:inline">{{ conv.user?.email }}</span>
                                        <span v-if="conv.unread_count > 0" class="px-1.5 py-0.5 text-[10px] rounded-full bg-red-500/20 text-red-400 font-medium">
                                            {{ conv.unread_count }} baru
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 truncate">{{ conv.last_message }}</p>
                                    <p class="text-[10px] text-gray-600 mt-0.5">{{ formatConvDate(conv.last_message_at) }}</p>
                                </div>
                            </div>
                            <i class="pi pi-chevron-right text-gray-600 shrink-0 ml-2"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Conversation Detail Modal -->
            <Dialog
                v-model:visible="showConversationModal"
                :header="`Pesan dari ${selectedUser?.name || ''}`"
                :modal="true"
                class="w-full max-w-lg"
            >
                <div class="space-y-3 max-h-80 overflow-y-auto pr-1 mb-4">
                    <div v-if="convMessages.length === 0" class="text-center py-8 text-gray-500">
                        <i class="pi pi-comments text-2xl mb-2"></i>
                        <p>Belum ada pesan.</p>
                    </div>
                    <div
                        v-for="msg in convMessages"
                        :key="msg.id"
                        :class="[
                            'flex',
                            msg.is_from_admin ? 'justify-start' : 'justify-end'
                        ]"
                    >
                        <div
                            :class="[
                                'max-w-[80%] p-3 rounded-lg',
                                msg.is_from_admin
                                    ? 'bg-navy-800 border border-navy-700 text-gray-300'
                                    : 'bg-brand-500/20 border border-brand-500/20 text-brand-100'
                            ]"
                        >
                            <p class="text-sm whitespace-pre-wrap">{{ msg.message }}</p>
                            <p class="text-[10px] text-gray-600 mt-1">{{ formatConvDate(msg.created_at) }}</p>
                        </div>
                    </div>
                </div>
                <div class="flex gap-2">
                    <textarea
                        v-model="replyMessage"
                        rows="2"
                        placeholder="Ketik balasan..."
                        class="flex-1 bg-navy-800 border border-navy-700 rounded-md px-3 py-2 text-sm text-gray-300 focus:outline-none focus:border-brand-500/50 resize-none"
                    ></textarea>
                    <button
                        @click="sendReply"
                        :disabled="!replyMessage.trim() || isSendingReply"
                        class="shrink-0 self-end px-3 py-2 bg-brand-500 text-white text-sm rounded-md hover:bg-brand-600 transition-all disabled:opacity-50"
                    >
                        <i v-if="isSendingReply" class="pi pi-spin pi-spinner"></i>
                        <i v-else class="pi pi-send"></i>
                    </button>
                </div>
            </Dialog>
        </template>

        <!-- ==================== CREATOR REQUESTS TAB ==================== -->
        <template v-if="activeTab === 'creator-requests'">
            <div class="space-y-4">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-lg font-semibold text-white">Creator Requests</h2>
                    <div class="flex gap-2">
                        <button
                            v-for="f in ['all', 'pending', 'approved', 'rejected']"
                            :key="f"
                            @click="creatorRequestFilter = f; fetchCreatorRequests()"
                            :class="[
                                'px-3 py-1.5 rounded-md text-xs font-medium transition-all',
                                creatorRequestFilter === f
                                    ? 'bg-brand-500/20 text-brand-400'
                                    : 'text-gray-500 hover:text-gray-300 hover:bg-navy-700/50'
                            ]"
                        >
                            {{ f.charAt(0).toUpperCase() + f.slice(1) }}
                        </button>
                    </div>
                </div>

                <div v-if="creatorRequestsLoading" class="space-y-3">
                    <div v-for="i in 3" :key="i" class="card p-5">
                        <div class="skeleton h-4 w-40 mb-3"></div>
                        <div class="skeleton h-3 w-full mb-2"></div>
                        <div class="skeleton h-3 w-3/4"></div>
                    </div>
                </div>

                <div v-else-if="creatorRequestsList && creatorRequestsList.length === 0" class="text-center py-10 text-gray-500">
                    <i class="pi pi-inbox text-3xl mb-3"></i>
                    <p>Tidak ada creator request.</p>
                </div>

                <div v-else class="space-y-3">
                    <div
                        v-for="req in creatorRequestsList"
                        :key="req.id"
                        class="card p-5 hover:border-navy-600 transition-colors"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-semibold text-white text-sm">{{ req.user?.name }}</span>
                                    <span class="text-xs text-gray-500">{{ req.user?.email }}</span>
                                </div>
                                <p class="text-sm text-gray-400 leading-relaxed mb-3">{{ req.reason }}</p>
                                <div class="flex items-center gap-3 text-xs text-gray-600">
                                    <span>{{ dayjs(req.created_at).format('D MMMM YYYY') }}</span>
                                    <span v-if="req.admin_note" class="text-orange-400">Catatan: {{ req.admin_note }}</span>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-2 shrink-0">
                                <span :class="[
                                    'px-2.5 py-1 rounded-full text-xs font-medium',
                                    req.status === 'pending' ? 'bg-yellow-500/10 text-yellow-400' :
                                    req.status === 'approved' ? 'bg-green-500/10 text-green-400' :
                                    'bg-red-500/10 text-red-400'
                                ]">
                                    {{ req.status.charAt(0).toUpperCase() + req.status.slice(1) }}
                                </span>
                                <div v-if="req.status === 'pending'" class="flex gap-2">
                                    <button
                                        @click="handleApproveCreatorRequest(req.id)"
                                        :disabled="processingId === req.id"
                                        class="px-3 py-1.5 rounded-md text-xs font-medium bg-green-500/10 text-green-400 hover:bg-green-500/20 border border-green-500/20 transition-all disabled:opacity-50"
                                    >
                                        <i class="pi pi-check mr-1"></i> Approve
                                    </button>
                                    <button
                                        @click="openRejectModal(req)"
                                        :disabled="processingId === req.id"
                                        class="px-3 py-1.5 rounded-md text-xs font-medium bg-red-500/10 text-red-400 hover:bg-red-500/20 border border-red-500/20 transition-all disabled:opacity-50"
                                    >
                                        <i class="pi pi-times mr-1"></i> Reject
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reject Modal -->
            <Transition name="mobile-menu">
                <div v-if="rejectModal.show" class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-black/60 backdrop-blur-sm">
                    <div class="card w-full max-w-md p-6">
                        <h3 class="text-lg font-semibold text-white mb-1">Tolak Creator Request</h3>
                        <p class="text-sm text-gray-500 mb-4">Dari: <span class="text-gray-300">{{ rejectModal.req?.user?.name }}</span></p>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Catatan (opsional)</label>
                        <textarea
                            v-model="rejectModal.note"
                            rows="3"
                            placeholder="Alasan penolakan..."
                            class="w-full bg-navy-800 border border-navy-700 rounded-md px-3 py-2 text-sm text-gray-300 focus:outline-none focus:border-brand-500/50 resize-none mb-4"
                        ></textarea>
                        <div class="flex gap-3 justify-end">
                            <button
                                @click="rejectModal.show = false"
                                class="px-4 py-2 rounded-md text-sm text-gray-400 hover:text-white hover:bg-navy-700 transition-all"
                            >Batal</button>
                            <button
                                @click="handleRejectCreatorRequest"
                                :disabled="processingId !== null"
                                class="px-4 py-2 rounded-md text-sm font-medium bg-red-500/10 text-red-400 hover:bg-red-500/20 border border-red-500/20 transition-all disabled:opacity-50"
                            >
                                <i class="pi pi-times mr-1"></i> Tolak
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </template>
    </div>
</template>

<script setup>
import { ref, onMounted, nextTick, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { adminApi, supportApi } from '@/services/api';
import { useToast } from 'vue-toastification';
import dayjs from '@/plugins/dayjs';

const toast = useToast();
import { useCampaign } from '@/composables/useCampaign';
import PendingReviewsSection from '@/views/admin/PendingReviewsSection.vue';
import AllCampaignsSection from '@/views/admin/AllCampaignsSection.vue';
import UsersSection from '@/views/admin/UsersSection.vue';
import Dialog from 'primevue/dialog';

const route = useRoute();
const router = useRouter();

const { formatCurrency } = useCampaign();

// Support Messages
const conversations = ref([]);
const supportLoading = ref(false);
const showConversationModal = ref(false);
const selectedUser = ref(null);
const convMessages = ref([]);
const replyMessage = ref('');
const isSendingReply = ref(false);

async function fetchConversations() {
    supportLoading.value = true;
    try {
        const res = await supportApi.getAdminConversations();
        conversations.value = res.data.data;
    } catch {
        conversations.value = [];
    } finally {
        supportLoading.value = false;
    }
}

async function openConversation(conv) {
    selectedUser.value = conv.user;
    showConversationModal.value = true;
    try {
        const res = await supportApi.getAdminConversation(conv.user_id);
        convMessages.value = res.data.data;
        // Refresh conversations to update unread count
        await fetchConversations();
    } catch {
        convMessages.value = [];
    }
}

async function sendReply() {
    if (!replyMessage.value.trim() || isSendingReply.value || !selectedUser.value) return;
    isSendingReply.value = true;
    try {
        // Need to find user_id from the selected conversation
        const conv = conversations.value.find(c => c.user_id === selectedUser.value?.id);
        if (!conv) return;
        await supportApi.adminReply(conv.user_id, replyMessage.value.trim());
        replyMessage.value = '';
        // Reload conversation
        const res = await supportApi.getAdminConversation(conv.user_id);
        convMessages.value = res.data.data;
    } catch {
        toast.error('Gagal mengirim balasan');
    } finally {
        isSendingReply.value = false;
    }
}

function getInitials(name) {
    if (!name) return '?';
    return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
}

function formatConvDate(date) {
    if (!date) return '-';
    return dayjs(date).format('D MMM YYYY, HH:mm');
}

const tabs = [
    { key: 'overview', label: 'Overview', icon: 'pi pi-chart-bar' },
    { key: 'pending-reviews', label: 'Pending Reviews', icon: 'pi pi-check-circle' },
    { key: 'campaigns', label: 'All Campaigns', icon: 'pi pi-verified' },
    { key: 'users', label: 'Users', icon: 'pi pi-users' },
    { key: 'support', label: 'Support', icon: 'pi pi-comments' },
    { key: 'creator-requests', label: 'Creator Requests', icon: 'pi pi-star' },
];
const activeTab = ref('overview');

function setActiveTab(key) {
    activeTab.value = key;
    
    const tabToRoute = {
        'overview': 'admin-dashboard',
        'pending-reviews': 'admin-pending-reviews',
        'campaigns': 'admin-campaigns',
        'users': 'admin-users',
        'creator-requests': 'admin-creator-requests',
        'support': 'admin-support',
    };
    const routeName = tabToRoute[key];
    if (routeName && route.name !== routeName) {
        router.push({ name: routeName });
    }

    // Always re-fetch data when switching tabs to ensure fresh data
    if (key === 'pending-reviews') fetchPendingReviews();
    if (key === 'campaigns') fetchAllCampaigns();
    if (key === 'users') fetchUsers();
    if (key === 'creator-requests') fetchCreatorRequests();
    if (key === 'support' && conversations.value.length === 0) fetchConversations();
}

// --- Overview State ---
const overviewStats = ref(null);
const loading = ref(true);
const error = ref(null);

const statuses = [
    { key: 'active', label: 'Active', value: 0, color: 'bg-green-500' },
    { key: 'success', label: 'Success', value: 0, color: 'bg-brand-500' },
    { key: 'rejected', label: 'Rejected', value: 0, color: 'bg-orange-500' },
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
        statuses[1].value = res.data.data.campaigns.success;
        statuses[2].value = res.data.data.campaigns.rejected;
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

async function fetchUsers(page = 1, search = '') {
    usersPage = page;
    usersLoading.value = true;
    usersError.value = null;
    try {
        const params = { page: usersPage };
        if (search) params.search = search;
        const res = await adminApi.getUsers(params);
        usersList.value = res.data.data;
        usersMeta.value = res.data.meta;
    } catch (err) {
        usersError.value = err.response?.data?.message || 'Gagal memuat users';
    } finally {
        usersLoading.value = false;
    }
}

// --- Creator Requests State ---
const creatorRequestsList = ref(null);
const creatorRequestsLoading = ref(false);
const creatorRequestFilter = ref('all');
const pendingCreatorRequestsCount = ref(0);
const processingId = ref(null);
const rejectModal = ref({ show: false, req: null, note: '' });

async function fetchCreatorRequests() {
    creatorRequestsLoading.value = true;
    try {
        const params = creatorRequestFilter.value !== 'all' ? { status: creatorRequestFilter.value } : {};
        const res = await adminApi.getCreatorRequests(params);
        creatorRequestsList.value = res.data.data;
    } catch (err) {
        creatorRequestsList.value = [];
    } finally {
        creatorRequestsLoading.value = false;
    }
}

async function fetchPendingCreatorRequestsCount() {
    try {
        const res = await adminApi.getCreatorRequests({ status: 'pending', per_page: 1 });
        pendingCreatorRequestsCount.value = res.data.meta?.total ?? 0;
    } catch {
        pendingCreatorRequestsCount.value = 0;
    }
}

async function handleApproveCreatorRequest(id) {
    processingId.value = id;
    try {
        await adminApi.approveCreatorRequest(id);
        await fetchCreatorRequests();
        await fetchPendingCreatorRequestsCount();
    } catch (err) {
        toast.error(err.response?.data?.message || 'Gagal approve request');
    } finally {
        processingId.value = null;
    }
}

function openRejectModal(req) {
    rejectModal.value = { show: true, req, note: '' };
}

async function handleRejectCreatorRequest() {
    if (!rejectModal.value.req) return;
    processingId.value = rejectModal.value.req.id;
    try {
        await adminApi.rejectCreatorRequest(rejectModal.value.req.id, rejectModal.value.note);
        rejectModal.value.show = false;
        await fetchCreatorRequests();
        await fetchPendingCreatorRequestsCount();
    } catch (err) {
        toast.error(err.response?.data?.message || 'Gagal reject request');
    } finally {
        processingId.value = null;
    }
}

watch(() => route.name, (newRouteName) => {
    const routeToTab = {
        'admin-dashboard': 'overview',
        'admin-pending-reviews': 'pending-reviews',
        'admin-campaigns': 'campaigns',
        'admin-users': 'users',
        'admin-creator-requests': 'creator-requests',
        'admin-support': 'support',
    };
    const tabKey = routeToTab[newRouteName];
    if (tabKey && activeTab.value !== tabKey) {
        activeTab.value = tabKey;
        if (tabKey === 'pending-reviews') fetchPendingReviews();
        if (tabKey === 'campaigns') fetchAllCampaigns();
        if (tabKey === 'users') fetchUsers();
        if (tabKey === 'creator-requests') fetchCreatorRequests();
        if (tabKey === 'support' && conversations.value.length === 0) fetchConversations();
    }
});

onMounted(async () => {
    const routeToTab = {
        'admin-dashboard': 'overview',
        'admin-pending-reviews': 'pending-reviews',
        'admin-campaigns': 'campaigns',
        'admin-users': 'users',
        'admin-creator-requests': 'creator-requests',
        'admin-support': 'support',
    };
    const tabKey = routeToTab[route.name] || 'overview';
    setActiveTab(tabKey);

    await fetchOverview();
    await fetchPendingCreatorRequestsCount();
});
</script>
