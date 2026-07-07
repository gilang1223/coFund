<template>
    <div class="container-page max-w-3xl mx-auto animate-fade-in">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-md bg-orange-500/10 flex items-center justify-center">
                    <i class="pi pi-user text-orange-400 text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-white">Profil Admin</h1>
                    <p class="text-gray-500 text-sm">Kelola informasi akun administrator</p>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <!-- Profile Info Card -->
            <div class="card p-6">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-16 h-16 rounded-full bg-orange-500/20 flex items-center justify-center text-2xl font-bold text-orange-400 shrink-0">
                        {{ getInitials(appStore.user?.name) }}
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-white">{{ appStore.user?.name }}</h2>
                        <p class="text-sm text-gray-500">{{ appStore.user?.email }}</p>
                        <span class="inline-block mt-1 text-xs font-medium px-2 py-0.5 rounded-sm bg-orange-500/10 text-orange-400">
                            Admin
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
                        class="px-4 py-2 bg-orange-500 text-white text-sm font-medium rounded-md hover:bg-orange-600 transition-all duration-200 disabled:opacity-60"
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

            <!-- Account Info Card -->
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <i class="pi pi-shield text-orange-400 text-sm"></i>
                    Informasi Akun
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-3 rounded-md bg-navy-800/50 border border-navy-700">
                        <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Role</p>
                        <p class="text-sm font-semibold text-orange-400">Administrator</p>
                    </div>
                    <div class="p-3 rounded-md bg-navy-800/50 border border-navy-700">
                        <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Status Email</p>
                        <p v-if="appStore.hasVerifiedEmail" class="text-sm font-semibold text-green-400">
                            <i class="pi pi-check-circle mr-1"></i> Terverifikasi
                        </p>
                        <p v-else class="text-sm font-semibold text-orange-400">
                            <i class="pi pi-exclamation-circle mr-1"></i> Belum Verifikasi
                        </p>
                    </div>
                    <div class="p-3 rounded-md bg-navy-800/50 border border-navy-700">
                        <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Bergabung</p>
                        <p class="text-sm font-semibold text-gray-300">{{ formatDate(appStore.user?.created_at) }}</p>
                    </div>
                </div>
            </div>

            <!-- Email Verification (if not verified) -->
            <div v-if="!appStore.hasVerifiedEmail" class="card p-6 border-orange-500/30">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-full bg-orange-500/10 flex items-center justify-center shrink-0">
                        <i class="pi pi-envelope text-orange-400 text-xl"></i>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-lg font-semibold text-white mb-1">Verifikasi Email</h2>
                        <p class="text-sm text-gray-400 mb-3">
                            Email <strong class="text-gray-300">{{ appStore.user?.email }}</strong> belum diverifikasi.
                        </p>

                        <div v-if="verifResendSuccess" class="bg-green-500/10 text-green-400 p-3 rounded-md text-sm border border-green-500/20 mb-3">
                            <i class="pi pi-check-circle mr-1"></i> Email verifikasi telah dikirim!
                        </div>
                        <div v-if="verifError" class="bg-orange-500/10 text-orange-400 p-3 rounded-md text-sm border border-orange-500/20 mb-3">
                            {{ verifError }}
                        </div>

                        <button
                            class="px-4 py-2 bg-orange-500 text-white text-sm font-medium rounded-md hover:bg-orange-600 transition-all disabled:opacity-50"
                            @click="sendVerificationEmail"
                            :disabled="isSendingVerif || verifCooldown > 0"
                        >
                            <i v-if="isSendingVerif" class="pi pi-spin pi-spinner mr-2"></i>
                            <i v-else class="pi pi-send mr-2"></i>
                            Kirim Email Verifikasi
                        </button>
                        <p v-if="verifCooldown > 0" class="text-xs text-gray-600 mt-2">
                            Kirim ulang dalam {{ verifCooldown }} detik
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useAppStore } from '@/stores/app';
import apiClient from '@/api/axios';
import dayjs from 'dayjs';

const appStore = useAppStore();

const form = ref({
    name: '',
    email: '',
});
const isUpdating = ref(false);
const updateError = ref(null);
const updateSuccess = ref(false);

// Email verification
const isSendingVerif = ref(false);
const verifResendSuccess = ref(false);
const verifError = ref(null);
const verifCooldown = ref(0);
let verifTimer = null;

function getInitials(name) {
    if (!name) return '?';
    return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
}

function formatDate(date) {
    if (!date) return '-';
    return dayjs(date).format('D MMMM YYYY');
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

onMounted(() => {
    if (appStore.user) {
        form.value.name = appStore.user.name;
        form.value.email = appStore.user.email;
    }
});

onUnmounted(() => {
    if (verifTimer) clearInterval(verifTimer);
});
</script>
