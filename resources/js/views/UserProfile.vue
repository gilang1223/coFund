<template>
    <div class="container-page max-w-3xl mx-auto animate-fade-in">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl md:text-3xl font-bold text-white">Profil Saya</h1>
            <p class="text-gray-500 mt-1">Kelola informasi akun Anda</p>
        </div>

        <div class="space-y-6">
            <!-- Profile Info Card -->
            <div class="card p-6">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-16 h-16 rounded-full bg-brand-500/20 flex items-center justify-center text-2xl font-bold text-brand-400 shrink-0">
                        {{ getInitials(appStore.user?.name) }}
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-white">{{ appStore.user?.name }}</h2>
                        <p class="text-sm text-gray-500">{{ appStore.user?.email }}</p>
                        <span
                            :class="[
                                'inline-block mt-1 text-xs font-medium px-2 py-0.5 rounded-sm',
                                appStore.user?.role === 'admin' ? 'bg-orange-500/10 text-orange-400' :
                                appStore.user?.role === 'creator' ? 'bg-brand-500/10 text-brand-400' :
                                'bg-blue-500/10 text-blue-400'
                            ]"
                        >
                            {{ appStore.user?.role === 'creator' ? 'Creator' : appStore.user?.role === 'admin' ? 'Admin' : 'Backer' }}
                        </span>
                    </div>
                </div>

                <!-- Edit Form -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Nama</label>
                        <input v-model="form.name" class="input-field" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Email</label>
                        <input v-model="form.email" class="input-field" disabled />
                    </div>
                </div>

                <div class="mt-4 flex gap-3">
                    <button
                        class="btn-brand"
                        @click="handleUpdateProfile"
                        :disabled="isUpdating"
                    >
                        <i v-if="isUpdating" class="pi pi-spin pi-spinner mr-2"></i>
                        <i v-else class="pi pi-check mr-2"></i>
                        Simpan Perubahan
                    </button>
                </div>

                <div v-if="updateError" class="mt-3 bg-orange-500/10 text-orange-400 p-3 rounded-md text-sm border border-orange-500/20">
                    {{ updateError }}
                </div>
                <div v-if="updateSuccess" class="mt-3 bg-green-500/10 text-green-400 p-3 rounded-md text-sm border border-green-500/20">
                    Profil berhasil diperbarui!
                </div>
            </div>

            <!-- Balance Card (hanya untuk backer & creator, bukan admin) -->
            <div v-if="!appStore.isAdmin" class="card p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Saldo</h2>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-3xl font-bold text-white">{{ formatCurrency(appStore.user?.balance || 0) }}</p>
                        <p class="text-sm text-gray-500 mt-1">Saldo tersedia</p>
                    </div>
                    <div class="flex gap-2">
                        <button
                            class="btn-ghost border border-navy-700"
                            @click="showTopUpDialog = true"
                        >
                            <i class="pi pi-plus mr-2 text-xs"></i>
                            Isi Saldo
                        </button>
                        <button
                            class="btn-ghost border border-navy-700"
                            @click="showWithdrawDialog = true"
                        >
                            <i class="pi pi-arrow-up mr-2 text-xs"></i>
                            Tarik Saldo
                        </button>
                    </div>
                </div>

                <!-- Withdrawal History -->
                <div v-if="withdrawals.length > 0" class="mt-6">
                    <h3 class="text-sm font-semibold text-gray-300 mb-3">Riwayat Penarikan</h3>
                    <div class="space-y-2">
                        <div
                            v-for="w in withdrawals"
                            :key="w.id"
                            class="flex items-center justify-between py-2 border-b border-navy-700/30 last:border-0"
                        >
                            <div class="flex items-center gap-3">
                                <div :class="['w-8 h-8 rounded-md flex items-center justify-center', w.status === 'success' ? 'bg-green-500/10' : w.status === 'failed' ? 'bg-red-500/10' : 'bg-yellow-500/10']">
                                    <i :class="['text-xs', w.status === 'success' ? 'pi pi-check text-green-400' : w.status === 'failed' ? 'pi pi-times text-red-400' : 'pi pi-clock text-yellow-400']"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-300">{{ w.bank_name }} - {{ w.bank_account_name }}</p>
                                    <p class="text-xs text-gray-600">{{ formatDate(w.created_at) }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-sm font-semibold text-gray-200">{{ formatCurrency(w.amount) }}</span>
                                <br>
                                <span :class="[
                                    'text-xs font-medium',
                                    w.status === 'success' ? 'text-green-400' :
                                    w.status === 'failed' ? 'text-red-400' :
                                    'text-yellow-400'
                                ]">
                                    {{ w.status === 'pending' ? 'Menunggu' : w.status === 'success' ? 'Sukses' : w.status === 'failed' ? 'Gagal' : w.status }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transaction History (all types: top-up, payment, withdrawal, refund, disbursement) -->
                <div v-if="transactions.length > 0" class="mt-6">
                    <h3 class="text-sm font-semibold text-gray-300 mb-3">Riwayat Transaksi</h3>
                    <div class="space-y-2">
                        <div
                            v-for="tx in transactions"
                            :key="tx.id"
                            class="flex items-center justify-between py-2 border-b border-navy-700/30 last:border-0"
                        >
                            <div class="flex items-center gap-3">
                                <div :class="['w-8 h-8 rounded-md flex items-center justify-center', getTxBgColor(tx.type)]">
                                    <i :class="['pi text-xs', getTxIcon(tx.type), getTxTextColor(tx.type)]"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-300">{{ getTxLabel(tx.type) }}</p>
                                    <p class="text-xs text-gray-600">{{ formatDate(tx.created_at) }}</p>
                                    <p v-if="tx.backing?.campaign?.title" class="text-xs text-gray-500 mt-0.5">{{ tx.backing.campaign.title }}</p>
                                </div>
                            </div>
                            <span :class="['text-sm font-semibold', getTxAmountColor(tx.type)]">
                                {{ getTxPrefix(tx.type) }}{{ formatCurrency(tx.amount) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Email Verification Status -->
            <div v-if="!appStore.hasVerifiedEmail" class="card p-6 border-orange-500/30">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-full bg-orange-500/10 flex items-center justify-center shrink-0">
                        <i class="pi pi-envelope text-orange-400 text-xl"></i>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-lg font-semibold text-white mb-1">Verifikasi Email Anda</h2>
                        <p class="text-sm text-gray-400 mb-3">
                            Email <strong class="text-gray-300">{{ appStore.user?.email }}</strong> belum diverifikasi.
                            Verifikasi diperlukan untuk melakukan donasi, membuat kampanye, dan mengakses fitur lainnya.
                        </p>

                        <div v-if="verifResendSuccess" class="bg-green-500/10 text-green-400 p-3 rounded-md text-sm border border-green-500/20 mb-3">
                            <i class="pi pi-check-circle mr-1"></i> Email verifikasi telah dikirim! Cek inbox atau folder spam.
                        </div>
                        <div v-if="verifError" class="bg-orange-500/10 text-orange-400 p-3 rounded-md text-sm border border-orange-500/20 mb-3">
                            {{ verifError }}
                        </div>

                        <div class="flex gap-3">
                            <button
                                class="px-4 py-2 bg-orange-500 text-white text-sm font-medium rounded-md hover:bg-orange-600 transition-all disabled:opacity-50"
                                @click="sendVerificationEmail"
                                :disabled="isSendingVerif || verifCooldown > 0"
                            >
                                <i v-if="isSendingVerif" class="pi pi-spin pi-spinner mr-2"></i>
                                <i v-else class="pi pi-send mr-2"></i>
                                Kirim Email Verifikasi
                            </button>
                            <router-link
                                to="/verify-email"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-navy-800 text-gray-300 text-sm font-medium rounded-md hover:bg-navy-700 transition-all border border-navy-700"
                            >
                                <i class="pi pi-external-link"></i>
                                Halaman Verifikasi
                            </router-link>
                        </div>
                        <p v-if="verifCooldown > 0" class="text-xs text-gray-600 mt-2">
                            Kirim ulang dalam {{ verifCooldown }} detik
                        </p>
                        <p class="text-xs text-gray-600 mt-2">
                            <i class="pi pi-info-circle mr-1"></i>
                            Belum menerima email? Cek folder spam atau klik tombol di atas untuk kirim ulang.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Creator Request (hanya untuk backer) -->
            <div v-if="appStore.isBacker" class="card p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Menjadi Creator</h2>
                <p class="text-sm text-gray-500 mb-4">
                    Ingin membuat kampanye sendiri? Ajukan permohonan menjadi creator. Admin akan mereview permohonan Anda.
                </p>

                <!-- Cek status request sebelumnya -->
                <div v-if="myRequests.length > 0" class="space-y-3 mb-4">
                    <h3 class="text-sm font-medium text-gray-300">Riwayat Permohonan:</h3>
                    <div
                        v-for="req in myRequests"
                        :key="req.id"
                        class="flex items-center justify-between p-3 rounded-md bg-navy-800/50 border border-navy-700"
                    >
                        <div>
                            <p class="text-sm text-gray-300">{{ req.reason }}</p>
                            <p class="text-xs text-gray-600 mt-1">{{ formatDate(req.created_at) }}</p>
                        </div>
                        <span
                            :class="[
                                'text-xs font-medium px-2 py-0.5 rounded-sm shrink-0 ml-2',
                                req.status === 'pending' ? 'bg-yellow-500/10 text-yellow-400' :
                                req.status === 'approved' ? 'bg-green-500/10 text-green-400' :
                                'bg-red-500/10 text-red-400'
                            ]"
                        >
                            {{ req.status === 'pending' ? 'Menunggu' : req.status === 'approved' ? 'Disetujui' : 'Ditolak' }}
                        </span>
                    </div>
                </div>

                <!-- Form request (hanya jika belum ada pending request) -->
                <div v-if="!hasPendingRequest && !hasBeenApproved">
                    <label class="block text-sm font-medium text-gray-300 mb-1">Alasan (mengapa ingin menjadi creator?)</label>
                    <textarea
                        v-model="creatorRequestReason"
                        rows="3"
                        class="input-field mb-3"
                        placeholder="Ceritakan mengapa Anda ingin menjadi creator..."
                    ></textarea>
                    <button
                        class="btn-brand"
                        @click="submitCreatorRequest"
                        :disabled="!creatorRequestReason.trim() || isSubmittingRequest"
                    >
                        <i v-if="isSubmittingRequest" class="pi pi-spin pi-spinner mr-2"></i>
                        <i v-else class="pi pi-star mr-2"></i>
                        Ajukan Permohonan
                    </button>
                    <div v-if="requestError" class="mt-3 bg-orange-500/10 text-orange-400 p-3 rounded-md text-sm">{{ requestError }}</div>
                    <div v-if="requestSuccess" class="mt-3 bg-green-500/10 text-green-400 p-3 rounded-md text-sm">Permohonan berhasil diajukan!</div>
                </div>

                <div v-else-if="hasPendingRequest" class="text-sm text-yellow-400 bg-yellow-500/10 p-3 rounded-md border border-yellow-500/20">
                    <i class="pi pi-clock mr-1"></i> Permohonan Anda sedang menunggu review admin.
                </div>
                <div v-else-if="hasBeenApproved" class="text-sm text-green-400 bg-green-500/10 p-3 rounded-md border border-green-500/20">
                    <i class="pi pi-check-circle mr-1"></i> Selamat! Anda sudah menjadi creator. Silakan buat kampanye.
                </div>
            </div>

            <!-- Danger Zone - Hapus Akun -->
            <div v-if="!appStore.isAdmin" class="card p-6 border-red-500/20">
                <h2 class="text-lg font-semibold text-red-400 mb-4 flex items-center gap-2">
                    <i class="pi pi-exclamation-triangle"></i>
                    Zona Berbahaya
                </h2>
                <p class="text-sm text-gray-500 mb-4">
                    Menghapus akun akan menghilangkan semua data Anda secara permanen. Tindakan ini tidak dapat dibatalkan.
                </p>

                <div v-if="deleteError" class="bg-orange-500/10 text-orange-400 p-3 rounded-md text-sm border border-orange-500/20 mb-3">
                    {{ deleteError }}
                </div>

                <button
                    class="px-4 py-2 bg-red-500/10 text-red-400 text-sm font-medium rounded-md hover:bg-red-500/20 border border-red-500/20 transition-all"
                    @click="openDeleteAccountModal"
                >
                    <i class="pi pi-trash mr-2"></i>
                    Hapus Akun
                </button>
            </div>
        </div>

        <!-- Top-Up Dialog -->
        <Transition name="fade">
            <div v-if="showTopUpDialog" class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-black/60 backdrop-blur-sm" @click.self="showTopUpDialog = false">
                <div class="card w-full max-w-md p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Isi Saldo</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Jumlah Top-Up</label>
                            <input
                                v-model.number="topUpAmount"
                                type="number"
                                class="input-field"
                                min="10000"
                                step="10000"
                                placeholder="Min. Rp 10.000"
                            />
                        </div>
                        <div class="flex gap-2 flex-wrap">
                            <button
                                v-for="amount in [50000, 100000, 200000, 500000]"
                                :key="amount"
                                @click="topUpAmount = amount"
                                :class="[
                                    'px-3 py-1.5 rounded-md text-sm font-medium border transition-all',
                                    topUpAmount === amount
                                        ? 'bg-brand-500/20 text-brand-400 border-brand-500/30'
                                        : 'bg-navy-800 text-gray-400 border-navy-700 hover:border-navy-600'
                                ]"
                            >
                                {{ formatCurrency(amount) }}
                            </button>
                        </div>
                        <button
                            class="btn-brand w-full"
                            @click="handleTopUp"
                            :disabled="!topUpAmount || topUpAmount < 10000 || isTopingUp"
                        >
                            <i v-if="isTopingUp" class="pi pi-spin pi-spinner mr-2"></i>
                            <i v-else class="pi pi-check mr-2"></i>
                            Konfirmasi Top-Up
                        </button>
                        <p class="text-xs text-gray-600 text-center">Top-Up bersifat simulasi. Saldo akan langsung ditambahkan.</p>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Withdraw Dialog -->
        <Transition name="fade">
            <div v-if="showWithdrawDialog" class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-black/60 backdrop-blur-sm" @click.self="showWithdrawDialog = false">
                <div class="card w-full max-w-md p-6">
                    <h3 class="text-lg font-semibold text-white mb-1">Tarik Saldo</h3>
                    <p class="text-sm text-gray-500 mb-4">Saldo tersedia: <strong class="text-white">{{ formatCurrency(appStore.user?.balance || 0) }}</strong></p>

                    <div v-if="withdrawError" class="bg-orange-500/10 text-orange-400 p-3 rounded-md text-sm border border-orange-500/20 mb-3">{{ withdrawError }}</div>
                    <div v-if="withdrawSuccess" class="bg-green-500/10 text-green-400 p-3 rounded-md text-sm border border-green-500/20 mb-3">{{ withdrawSuccess }}</div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Jumlah Penarikan</label>
                            <input
                                v-model.number="withdrawForm.amount"
                                type="number"
                                class="input-field"
                                min="50000"
                                step="10000"
                                placeholder="Min. Rp 50.000"
                                :max="appStore.user?.balance || 0"
                            />
                            <p class="text-xs text-gray-600 mt-1">Min. Rp 50.000 · Biaya admin 1%</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Nama Bank</label>
                            <input v-model="withdrawForm.bank_name" class="input-field" placeholder="Contoh: BCA, Mandiri, BRI" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Nomor Rekening</label>
                            <input v-model="withdrawForm.bank_account_number" class="input-field" placeholder="Nomor rekening" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Nama Pemilik Rekening</label>
                            <input v-model="withdrawForm.bank_account_name" class="input-field" placeholder="Nama sesuai rekening" />
                        </div>
                        <button
                            class="btn-brand w-full"
                            @click="handleWithdraw"
                            :disabled="!canWithdraw || isWithdrawing"
                        >
                            <i v-if="isWithdrawing" class="pi pi-spin pi-spinner mr-2"></i>
                            <i v-else class="pi pi-arrow-up mr-2"></i>
                            Ajukan Penarikan
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Delete Account Confirmation Modal -->
        <Transition name="fade">
            <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-black/60 backdrop-blur-sm" @click.self="showDeleteModal = false">
                <div class="card w-full max-w-sm p-6">
                    <div class="text-center mb-4">
                        <div class="w-14 h-14 rounded-full bg-red-500/10 flex items-center justify-center mx-auto mb-3">
                            <i class="pi pi-exclamation-triangle text-red-400 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-white mb-1">Hapus Akun</h3>
                        <p v-if="canDeleteAccount" class="text-sm text-gray-400">
                            Apakah Anda yakin ingin menghapus akun Anda? Semua data akan hilang secara permanen. Tindakan ini tidak dapat dibatalkan.
                        </p>
                        <p v-else class="text-sm text-orange-400">
                            Saldo Anda masih <strong>{{ formatCurrency(appStore.user?.balance || 0) }}</strong>. Silakan tarik saldo terlebih dahulu sebelum menghapus akun.
                        </p>
                    </div>
                    <div v-if="deleteAccountError" class="bg-orange-500/10 text-orange-400 p-3 rounded-md text-sm border border-orange-500/20 mb-3">{{ deleteAccountError }}</div>
                    <div class="flex gap-3">
                        <button
                            @click="showDeleteModal = false"
                            class="flex-1 px-4 py-2.5 rounded-md text-sm font-medium text-gray-400 hover:text-white hover:bg-navy-700 border border-navy-700 transition-all"
                        >
                            Tutup
                        </button>
                        <button
                            v-if="canDeleteAccount"
                            @click="handleDeleteAccount"
                            :disabled="isDeletingAccount"
                            class="flex-1 px-4 py-2.5 rounded-md text-sm font-medium bg-red-500/10 text-red-400 hover:bg-red-500/20 border border-red-500/20 transition-all disabled:opacity-50"
                        >
                            <i v-if="isDeletingAccount" class="pi pi-spin pi-spinner mr-1"></i>
                            <i v-else class="pi pi-trash mr-1"></i>
                            Hapus Akun
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAppStore } from '@/stores/app';
import { useCampaign } from '@/composables/useCampaign';
import { creatorRequestApi, withdrawalApi, authApi } from '@/services/api';
import apiClient from '@/api/axios';
import { useToast } from 'vue-toastification';
import dayjs from 'dayjs';

const toast = useToast();
const router = useRouter();
const appStore = useAppStore();
const { formatCurrency } = useCampaign();

// Profile form
const form = ref({
    name: '',
    email: '',
});
const isUpdating = ref(false);
const updateError = ref(null);
const updateSuccess = ref(false);

// Creator request
const creatorRequestReason = ref('');
const isSubmittingRequest = ref(false);
const requestError = ref(null);
const requestSuccess = ref(false);
const myRequests = ref([]);

// Top-up
const showTopUpDialog = ref(false);
const topUpAmount = ref(50000);
const isTopingUp = ref(false);
const transactions = ref([]);

// Withdraw
const showWithdrawDialog = ref(false);
const isWithdrawing = ref(false);
const withdrawError = ref(null);
const withdrawSuccess = ref(null);
const withdrawals = ref([]);
const withdrawForm = ref({
    amount: 50000,
    bank_name: '',
    bank_account_number: '',
    bank_account_name: '',
});

const canWithdraw = computed(() =>
    withdrawForm.value.amount >= 50000 &&
    withdrawForm.value.bank_name &&
    withdrawForm.value.bank_account_number &&
    withdrawForm.value.bank_account_name
);

// Delete Account
const showDeleteModal = ref(false);
const isDeletingAccount = ref(false);
const deleteAccountError = ref(null);
const deleteError = ref(null);

const canDeleteAccount = computed(() => {
    return (appStore.user?.balance || 0) <= 0;
});

// Email verification
const isSendingVerif = ref(false);
const verifResendSuccess = ref(false);
const verifError = ref(null);
const verifCooldown = ref(0);
let verifTimer = null;

const hasPendingRequest = computed(() =>
    myRequests.value.some(r => r.status === 'pending')
);
const hasBeenApproved = computed(() =>
    appStore.user?.role === 'creator'
);



function getInitials(name) {
    if (!name) return '?';
    return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
}

function formatDate(date) {
    if (!date) return '-';
    return dayjs(date).format('DD MMM YYYY HH:mm');
}

// Transaction type helpers
function getTxLabel(type) {
    const labels = {
        payment: 'Pembayaran Donasi',
        disbursement: 'Pencairan Dana',
        refund: 'Pengembalian Dana',
        platform_fee: 'Biaya Platform',
        top_up: 'Top-Up Saldo',
        withdrawal: 'Penarikan Dana',
    };
    return labels[type] || type;
}

function getTxIcon(type) {
    const icons = {
        payment: 'pi-arrow-right',
        disbursement: 'pi-arrow-up',
        refund: 'pi-arrow-left',
        platform_fee: 'pi-percentage',
        top_up: 'pi-arrow-down',
        withdrawal: 'pi-arrow-up',
    };
    return icons[type] || 'pi-question';
}

function getTxTextColor(type) {
    const colors = {
        payment: 'text-brand-400',
        disbursement: 'text-green-400',
        refund: 'text-orange-400',
        platform_fee: 'text-purple-400',
        top_up: 'text-green-400',
        withdrawal: 'text-yellow-400',
    };
    return colors[type] || 'text-gray-400';
}

function getTxBgColor(type) {
    const colors = {
        payment: 'bg-brand-500/10',
        disbursement: 'bg-green-500/10',
        refund: 'bg-orange-500/10',
        platform_fee: 'bg-purple-500/10',
        top_up: 'bg-green-500/10',
        withdrawal: 'bg-yellow-500/10',
    };
    return colors[type] || 'bg-navy-700/50';
}

function getTxAmountColor(type) {
    const colors = {
        payment: 'text-brand-400',
        disbursement: 'text-green-400',
        refund: 'text-green-400',
        platform_fee: 'text-purple-400',
        top_up: 'text-green-400',
        withdrawal: 'text-gray-300',
    };
    return colors[type] || 'text-gray-400';
}

function getTxPrefix(type) {
    const prefixes = {
        top_up: '+',
        refund: '+',
        disbursement: '+',
        payment: '-',
        withdrawal: '-',
        platform_fee: '-',
    };
    return prefixes[type] || '';
}

async function handleUpdateProfile() {
    isUpdating.value = true;
    updateError.value = null;
    updateSuccess.value = false;
    try {
        const res = await apiClient.put('/user/profile', { name: form.value.name });
        appStore.setUser(res.data.data);
        updateSuccess.value = true;
    } catch (err) {
        updateError.value = err.response?.data?.message || 'Gagal memperbarui profil';
    } finally {
        isUpdating.value = false;
    }
}

async function sendVerificationEmail() {
    if (isSendingVerif.value || verifCooldown.value > 0) return;

    isSendingVerif.value = true;
    verifResendSuccess.value = false;
    verifError.value = null;
    try {
        await apiClient.post('/email/verification-notification');
        verifResendSuccess.value = true;
        verifCooldown.value = 60;
        if (verifTimer) clearInterval(verifTimer);
        verifTimer = setInterval(() => {
            verifCooldown.value--;
            if (verifCooldown.value <= 0) {
                clearInterval(verifTimer);
                verifTimer = null;
            }
        }, 1000);
    } catch (err) {
        verifError.value = err.response?.data?.message || 'Gagal mengirim email verifikasi';
    } finally {
        isSendingVerif.value = false;
    }
}

async function submitCreatorRequest() {
    isSubmittingRequest.value = true;
    requestError.value = null;
    requestSuccess.value = false;
    try {
        await creatorRequestApi.submit({ reason: creatorRequestReason.value });
        requestSuccess.value = true;
        creatorRequestReason.value = '';
        await fetchMyRequests();
    } catch (err) {
        requestError.value = err.response?.data?.message || 'Gagal mengajukan permohonan';
    } finally {
        isSubmittingRequest.value = false;
    }
}

async function fetchMyRequests() {
    try {
        const res = await creatorRequestApi.getMyRequests();
        myRequests.value = res.data.data;
    } catch {
        myRequests.value = [];
    }
}

async function handleTopUp() {
    if (!topUpAmount.value || topUpAmount.value < 10000) return;
    isTopingUp.value = true;
    try {
        const res = await apiClient.post('/user/top-up', { amount: topUpAmount.value });
        appStore.setUser(res.data.data.user || res.data.data);
        topUpAmount.value = 50000;
        showTopUpDialog.value = false;
        await fetchTransactions();
    } catch (err) {
        toast.error(err.response?.data?.message || 'Top-Up gagal');
    } finally {
        isTopingUp.value = false;
    }
}

async function fetchTransactions() {
    try {
        const res = await apiClient.get('/transactions');
        transactions.value = res.data.data;
    } catch {
        transactions.value = [];
    }
}

async function fetchWithdrawals() {
    try {
        const res = await withdrawalApi.getAll();
        withdrawals.value = res.data.data;
    } catch {
        withdrawals.value = [];
    }
}

async function handleWithdraw() {
    if (!canWithdraw.value) return;
    isWithdrawing.value = true;
    withdrawError.value = null;
    withdrawSuccess.value = null;
    try {
        await withdrawalApi.create({
            amount: withdrawForm.value.amount,
            bank_name: withdrawForm.value.bank_name,
            bank_account_number: withdrawForm.value.bank_account_number,
            bank_account_name: withdrawForm.value.bank_account_name,
        });
        withdrawSuccess.value = 'Permohonan penarikan berhasil diajukan!';
        // Refresh user balance
        await appStore.fetchUser();
        await fetchWithdrawals();
        await fetchTransactions();
        // Reset form
        withdrawForm.value = {
            amount: 50000,
            bank_name: '',
            bank_account_number: '',
            bank_account_name: '',
        };
        setTimeout(() => {
            showWithdrawDialog.value = false;
            withdrawSuccess.value = null;
        }, 2000);
    } catch (err) {
        withdrawError.value = err.response?.data?.message || 'Penarikan gagal';
    } finally {
        isWithdrawing.value = false;
    }
}

function openDeleteAccountModal() {
    deleteError.value = null;
    deleteAccountError.value = null;
    showDeleteModal.value = true;
}

async function handleDeleteAccount() {
    if (!canDeleteAccount.value) return;
    isDeletingAccount.value = true;
    deleteAccountError.value = null;
    try {
        await authApi.deleteAccount();
        appStore.clearUser();
        router.push('/');
        toast.success('Akun berhasil dihapus. Terima kasih telah menggunakan CoFund.');
    } catch (err) {
        deleteAccountError.value = err.response?.data?.message || 'Gagal menghapus akun';
    } finally {
        isDeletingAccount.value = false;
    }
}

onMounted(async () => {
    if (appStore.user) {
        form.value.name = appStore.user.name;
        form.value.email = appStore.user.email;
    }
    if (appStore.isBacker) {
        await fetchMyRequests();
    }
    await fetchTransactions();
    await fetchWithdrawals();
});

onUnmounted(() => {
    if (verifTimer) clearInterval(verifTimer);
});
</script>
