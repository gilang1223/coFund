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
                <h1 class="text-2xl font-bold text-white">Lupa Password</h1>
                <p class="text-gray-500 mt-1">Masukkan email Anda untuk menerima link reset password</p>
            </div>

            <div class="card p-8">
                <!-- Success state -->
                <div v-if="sent" class="text-center py-4">
                    <div class="w-16 h-16 rounded-full bg-green-500/10 flex items-center justify-center mx-auto mb-4">
                        <i class="pi pi-envelope text-green-400 text-2xl"></i>
                    </div>
                    <h2 class="text-lg font-semibold text-white mb-2">Cek Email Anda</h2>
                    <p class="text-sm text-gray-400 leading-relaxed">
                        Jika email <strong class="text-gray-300">{{ email }}</strong> terdaftar, kami telah mengirimkan link reset password. Silakan cek inbox (atau folder spam) Anda.
                    </p>
                    <button
                        @click="sent = false; email = ''"
                        class="mt-6 text-sm text-brand-400 hover:text-brand-300 transition-colors"
                    >
                        Kirim ulang ke email lain
                    </button>
                </div>

                <!-- Form -->
                <form v-else @submit.prevent="handleSubmit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Email</label>
                        <div class="relative">
                            <i class="pi pi-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm"></i>
                            <input
                                v-model="email"
                                type="email"
                                placeholder="your@email.com"
                                class="input-field pl-10 transition-all duration-200"
                                :class="{ 'border-orange-500/50 focus:border-orange-500': errors.email, 'focus:border-brand-500/50': !errors.email }"
                                :disabled="isLoading"
                            />
                        </div>
                        <Transition name="slide-down">
                            <small v-if="errors.email" class="text-orange-400 flex items-center gap-1 mt-1">
                                <i class="pi pi-exclamation-circle text-xs"></i> {{ errors.email }}
                            </small>
                        </Transition>
                    </div>

                    <!-- Error Alert -->
                    <Transition name="slide-down">
                        <div v-if="error" class="bg-orange-500/10 text-orange-400 p-3 rounded-md text-sm border border-orange-500/20 flex items-start gap-2">
                            <i class="pi pi-exclamation-triangle mt-0.5 shrink-0"></i>
                            <span>{{ error }}</span>
                        </div>
                    </Transition>

                    <button
                        type="submit"
                        :disabled="isLoading"
                        class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-brand-500 text-white font-medium rounded-md hover:bg-brand-600 transition-all duration-200 disabled:opacity-60 disabled:cursor-not-allowed shadow-lg shadow-brand-500/20 hover:shadow-brand-500/30"
                    >
                        <i v-if="isLoading" class="pi pi-spin pi-spinner"></i>
                        <i v-else class="pi pi-send"></i>
                        {{ isLoading ? 'Mengirim...' : 'Kirim Link Reset' }}
                    </button>
                </form>

                <div class="mt-6 text-center text-sm">
                    <span class="text-gray-500">Ingat password?</span>
                    <router-link to="/login" class="text-brand-400 font-medium hover:text-brand-300 transition-colors ml-1">
                        Login
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { passwordResetApi } from '@/services/api';

const email = ref('');
const error = ref(null);
const errors = ref({});
const isLoading = ref(false);
const sent = ref(false);

async function handleSubmit() {
    errors.value = {};
    error.value = null;

    if (!email.value) {
        errors.value.email = 'Email wajib diisi';
        return;
    }

    isLoading.value = true;
    try {
        await passwordResetApi.sendResetLink(email.value);
        sent.value = true;
    } catch (err) {
        error.value = err.response?.data?.message || 'Gagal mengirim link reset. Silakan coba lagi.';
    } finally {
        isLoading.value = false;
    }
}
</script>
