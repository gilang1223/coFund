<template>
    <div class="animate-fade-in">
        <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
            <div>
                <h2 class="text-xl font-semibold text-white">Manage Users</h2>
                <p class="text-gray-500 text-sm mt-0.5">Daftar seluruh pengguna platform</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="relative">
                    <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm"></i>
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Cari nama atau email..."
                        class="input-field pl-9 py-1.5 text-sm w-52"
                        @input="debouncedSearch"
                    />
                </div>
                <button @click="$emit('refresh')" class="text-sm text-brand-400 hover:text-brand-300 transition-colors">
                    <i class="pi pi-refresh mr-1"></i> Refresh
                </button>
            </div>
        </div>

        <!-- Skeleton -->
        <div v-if="loading" class="space-y-2">
            <div v-for="i in 5" :key="i" class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="skeleton h-10 w-10 rounded-full shrink-0"></div>
                    <div class="flex-1 space-y-1">
                        <div class="skeleton h-4 w-36"></div>
                        <div class="skeleton h-3 w-48"></div>
                    </div>
                    <div class="skeleton h-5 w-16 rounded-sm"></div>
                    <div class="skeleton h-4 w-20"></div>
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
        <div v-else-if="!users || users.length === 0" class="card p-10 text-center">
            <div class="w-16 h-16 rounded-md bg-navy-800 flex items-center justify-center mx-auto mb-4">
                <i class="pi pi-users text-gray-600 text-2xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-300 mb-1">Tidak ada user</h3>
            <p class="text-gray-500 text-sm">Belum ada pengguna yang terdaftar.</p>
        </div>

        <!-- Users Table -->
        <div v-else class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-xs text-gray-500 uppercase tracking-wider border-b border-navy-700/50">
                        <th class="pb-3 font-medium">User</th>
                        <th class="pb-3 font-medium">Email</th>
                        <th class="pb-3 font-medium">Role</th>
                        <th class="pb-3 font-medium">Status</th>
                        <th class="pb-3 font-medium">Balance</th>
                        <th class="pb-3 font-medium">Joined</th>
                        <th class="pb-3 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-navy-800/50">
                    <tr
                        v-for="user in users"
                        :key="user.id"
                        class="hover:bg-navy-800/30 transition-colors"
                    >
                        <td class="py-3 pr-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold shrink-0"
                                    :class="user.role === 'admin' ? 'bg-orange-500/20 text-orange-400' : user.role === 'creator' ? 'bg-brand-500/20 text-brand-400' : 'bg-blue-500/20 text-blue-400'"
                                >
                                    {{ getInitials(user.name) }}
                                </div>
                                <span class="text-sm text-white font-medium">{{ user.name }}</span>
                            </div>
                        </td>
                        <td class="py-3 pr-4">
                            <span class="text-sm text-gray-400">{{ user.email }}</span>
                        </td>
                        <td class="py-3 pr-4">
                            <span
                                :class="[
                                    'text-xs font-medium px-2 py-0.5 rounded-sm',
                                    user.role === 'admin' ? 'bg-orange-500/10 text-orange-400' :
                                    user.role === 'creator' ? 'bg-brand-500/10 text-brand-400' :
                                    'bg-blue-500/10 text-blue-400'
                                ]"
                            >{{ user.role }}</span>
                        </td>
                        <td class="py-3 pr-4">
                            <span
                                :class="[
                                    'text-xs font-medium px-2 py-0.5 rounded-sm',
                                    user.is_suspended ? 'bg-red-500/10 text-red-400' : 'bg-green-500/10 text-green-400'
                                ]"
                            >{{ user.is_suspended ? 'Suspended' : 'Active' }}</span>
                        </td>
                        <td class="py-3 pr-4">
                            <span class="text-sm text-gray-300">{{ formatCurrency(user.balance || 0) }}</span>
                        </td>
                        <td class="py-3">
                            <span class="text-sm text-gray-500">{{ formatDate(user.created_at) }}</span>
                        </td>
                        <td class="py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <button
                                    @click="openTransactionModal(user)"
                                    class="p-1.5 rounded-md text-gray-500 hover:text-brand-400 hover:bg-brand-500/10 transition-all"
                                    v-tooltip.top="'Lihat Riwayat Transaksi'"
                                >
                                    <i class="pi pi-receipt text-sm"></i>
                                </button>
                                <button
                                    v-if="user.role !== 'admin'"
                                    @click="handleToggleSuspend(user)"
                                    :disabled="actionLoading === user.id"
                                    class="p-1.5 rounded-md transition-all"
                                    :class="user.is_suspended ? 'text-green-400 hover:bg-green-500/10' : 'text-orange-400 hover:bg-orange-500/10'"
                                    v-tooltip.top="user.is_suspended ? 'Aktifkan' : 'Suspend'"
                                >
                                    <i v-if="actionLoading === user.id" class="pi pi-spin pi-spinner text-sm"></i>
                                    <i v-else :class="[user.is_suspended ? 'pi pi-check-circle' : 'pi pi-ban', 'text-sm']"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="meta && meta.last_page > 1" class="flex justify-center pt-6">
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

        <!-- Transaction History Modal -->
        <Dialog
            v-model:visible="showTransactionModal"
            :header="`Riwayat Transaksi - ${selectedUser?.name || ''}`"
            :modal="true"
            class="w-full max-w-2xl"
        >
            <div v-if="txLoading" class="py-8 text-center text-gray-500">
                <i class="pi pi-spin pi-spinner text-2xl mb-2"></i>
                <p>Memuat transaksi...</p>
            </div>
            <div v-else-if="userTransactions.length === 0" class="py-8 text-center text-gray-500">
                <i class="pi pi-receipt text-3xl mb-2 text-gray-600"></i>
                <p>Tidak ada transaksi untuk user ini.</p>
            </div>
            <div v-else class="space-y-2 max-h-96 overflow-y-auto pr-1">
                <div
                    v-for="tx in userTransactions"
                    :key="tx.id"
                    class="flex items-center justify-between p-3 rounded-md bg-navy-800/50 border border-navy-700"
                >
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-md flex items-center justify-center shrink-0" :class="tx.type === 'top_up' ? 'bg-green-500/10' : tx.type === 'payment' ? 'bg-brand-500/10' : tx.type === 'disbursement' ? 'bg-green-500/10' : 'bg-orange-500/10'">
                            <i :class="['pi text-xs', getTxTypeIcon(tx.type), getTxTypeColor(tx.type)]"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-300">{{ getTxTypeLabel(tx.type) }}</p>
                            <p class="text-xs text-gray-600">{{ tx.reference }}</p>
                            <p v-if="tx.backing?.campaign" class="text-xs text-gray-600">{{ tx.backing.campaign.title }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p :class="['text-sm font-semibold', getTxTypeColor(tx.type)]">
                            {{ tx.type === 'refund' || tx.type === 'top_up' || tx.type === 'disbursement' ? '+' : '-' }}
                            {{ formatCurrency(tx.amount) }}
                        </p>
                        <p class="text-xs text-gray-600">{{ formatTxDate(tx.created_at) }}</p>
                    </div>
                </div>
            </div>
        </Dialog>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useCampaign } from '@/composables/useCampaign';
import { adminApi } from '@/services/api';
import { useToast } from 'vue-toastification';
import Dialog from 'primevue/dialog';
import dayjs from '@/plugins/dayjs';

const toast = useToast();

const props = defineProps({
    loading: Boolean,
    users: Array,
    meta: Object,
    error: String,
});

const emit = defineEmits(['fetch', 'refresh']);

const searchQuery = ref('');
let debounceTimer = null;

function debouncedSearch() {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        emit('fetch', 1, searchQuery.value);
    }, 400);
}

const { formatCurrency } = useCampaign();

const actionLoading = ref(null);
const selectedUser = ref(null);
const showTransactionModal = ref(false);
const userTransactions = ref([]);
const txLoading = ref(false);

function getInitials(name) {
    if (!name) return '?';
    return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
}

function formatDate(date) {
    if (!date) return '-';
    return dayjs(date).format('D MMM YYYY');
}

function goToPage(page) {
    emit('fetch', page);
}

async function handleToggleSuspend(user) {
    actionLoading.value = user.id;
    try {
        if (user.is_suspended) {
            await adminApi.reactivateUser(user.id);
        } else {
            await adminApi.suspendUser(user.id);
        }
        emit('refresh');
    } catch (err) {
        toast.error(err.response?.data?.message || 'Gagal memproses user');
    } finally {
        actionLoading.value = null;
    }
}

async function openTransactionModal(user) {
    selectedUser.value = user;
    showTransactionModal.value = true;
    txLoading.value = true;
    userTransactions.value = [];
    try {
        const res = await adminApi.getUserTransactions(user.id);
        userTransactions.value = res.data.data;
    } catch {
        userTransactions.value = [];
    } finally {
        txLoading.value = false;
    }
}

function formatTxDate(date) {
    if (!date) return '-';
    return dayjs(date).format('DD MMM YYYY HH:mm');
}

function getTxTypeLabel(type) {
    const labels = {
        payment: 'Pembayaran Donasi',
        top_up: 'Top-Up Saldo',
        disbursement: 'Pencairan Dana',
        refund: 'Pengembalian Dana',
        platform_fee: 'Biaya Platform',
    };
    return labels[type] || type;
}

function getTxTypeColor(type) {
    const colors = {
        payment: 'text-brand-400',
        top_up: 'text-green-400',
        disbursement: 'text-green-400',
        refund: 'text-orange-400',
        platform_fee: 'text-purple-400',
    };
    return colors[type] || 'text-gray-400';
}

function getTxTypeIcon(type) {
    const icons = {
        payment: 'pi-arrow-right',
        top_up: 'pi-arrow-down',
        disbursement: 'pi-arrow-up',
        refund: 'pi-arrow-left',
        platform_fee: 'pi-percentage',
    };
    return icons[type] || 'pi-question';
}
</script>
