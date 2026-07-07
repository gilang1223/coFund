<template>
    <div class="min-h-screen flex items-center justify-center px-4 py-12 bg-navy-950 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-96 h-96 bg-brand-500/5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-brand-500/3 rounded-full blur-3xl"></div>

        <div class="max-w-md w-full animate-fade-in relative">
            <div class="text-center mb-8">
                <router-link to="/" class="inline-flex items-center space-x-2.5 mb-6 group">
                    <div class="w-10 h-10 rounded-md bg-brand-500/10 flex items-center justify-center group-hover:bg-brand-500/20 transition-colors">
                        <i class="pi pi-heart-fill text-brand-500 text-lg"></i>
                    </div>
                    <span class="text-xl font-bold text-white">CoFund</span>
                </router-link>
            </div>

            <div class="card p-8">
                <!-- Verified state -->
                <div v-if="isVerified" class="text-center py-4">
                    <div class="w-16 h-16 rounded-full bg-green-500/10 flex items-center justify-center mx-auto mb-4">
                        <i class="pi pi-check-circle text-green-400 text-2xl"></i>
                    </div>
                    <h2 class="text-lg font-semibold text-white mb-2">Email Terverifikasi! 🎉</h2>
                    <p class="text-sm text-gray-400 mb-6">Email Anda telah berhasil diverifikasi. Sekarang Anda bisa menggunakan semua fitur CoFund.</p>
                    <router-link
                        to="/dashboard"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-brand-500 text-white font-medium rounded-md hover:bg-brand-600 transition-all duration-200"
                    >
                        <i class="pi pi-arrow-right"></i>
                        Lanjut ke Dashboard
                    </router-link>
                </div>

                <!-- Send verification state -->
                <div v-else class="text-center py-4">
                    <div class="w-16 h-16 rounded-full bg-brand-500/10 flex items-center justify-center mx-auto mb-4">
                        <i class="pi pi-envelope text-brand-400 text-2xl"></i>
                    </div>
                    <h2 class="text-lg font-semibold text-white mb-2">Verifikasi Email Anda</h2>
                    <p class="text-sm text-gray-400 leading-relaxed mb-4">
                        Kami telah mengirimkan email verifikasi ke <strong class="text-gray-300">{{ userEmail }}</strong>.
                        Silakan cek inbox (atau folder spam) Anda dan klik tautan verifikasi.
                    </p>

                    <div v-if="resendSuccess" class="bg-green-500/10 text-green-400 p-3 rounded-md text-sm border border-green-500/20 mb-4">
                        <i class="pi pi-check-circle mr-1"></i> Email verifikasi telah dikirim ulang!
                    </div>

                    <button
                        @click="resendVerification"
                        :disabled="isResending || cooldown > 0"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-brand-500 text-white font-medium rounded-md hover:bg-brand-600 transition-all duration-200 disabled:opacity-60 disabled:cursor-not-allowed"
                    >
                        <i v-if="isResending" class="pi pi-spin pi-spinner"></i>
                        <i v-else class="pi pi-refresh"></i>
                        {{ isResending ? 'Mengirim...' : cooldown > 0 ? `Kirim Ulang (${cooldown}s)` : 'Kirim Ulang Email' }}
                    </button>

                    <p class="text-xs text-gray-600 mt-4">
                        Belum menerima email? Cek folder spam atau tunggu beberapa menit.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAppStore } from '@/stores/app';
import { authApi } from '@/services/api';

const router = useRouter();
const route = useRoute();
const appStore = useAppStore();

const isVerified = ref(false);
const isResending = ref(false);
const resendSuccess = ref(false);
const cooldown = ref(0);
let cooldownTimer = null;

const userEmail = ref('');

onMounted(async () => {
    userEmail.value = appStore.user?.email || '';

    // Check if user is already verified
    try {
        const res = await authApi.getUser();
        if (res.data.data?.email_verified_at) {
            isVerified.value = true;
        }
    } catch {
        // Not authenticated or error
    }

    // Handle query params from redirect after email verification
    if (route.query.verified === 'success') {
        isVerified.value = true;
        // Refresh user data
        await appStore.fetchUser();
    }
});

async function resendVerification() {
    if (isResending.value || cooldown.value > 0) return;

    isResending.value = true;
    resendSuccess.value = false;
    try {
        await authApi.sendVerificationEmail();
        resendSuccess.value = true;
        // Start cooldown
        cooldown.value = 60;
        if (cooldownTimer) clearInterval(cooldownTimer);
        cooldownTimer = setInterval(() => {
            cooldown.value--;
            if (cooldown.value <= 0) {
                clearInterval(cooldownTimer);
                cooldownTimer = null;
            }
        }, 1000);
    } catch (err) {
        // Handle error silently
    } finally {
        isResending.value = false;
    }
}
</script>
