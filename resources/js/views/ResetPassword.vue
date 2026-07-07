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
                <h1 class="text-2xl font-bold text-white">Reset Password</h1>
                <p class="text-gray-500 mt-1">Buat password baru untuk akun Anda</p>
            </div>

            <div class="card p-8">
                <!-- Success state -->
                <div v-if="success" class="text-center py-4">
                    <div class="w-16 h-16 rounded-full bg-green-500/10 flex items-center justify-center mx-auto mb-4">
                        <i class="pi pi-check-circle text-green-400 text-2xl"></i>
                    </div>
                    <h2 class="text-lg font-semibold text-white mb-2">Password Berhasil Diubah!</h2>
                    <p class="text-sm text-gray-400 mb-6">Silakan login dengan password baru Anda.</p>
                    <router-link
                        to="/login"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-brand-500 text-white font-medium rounded-md hover:bg-brand-600 transition-all duration-200"
                    >
                        <i class="pi pi-sign-in"></i>
                        Login Sekarang
                    </router-link>
                </div>

                <!-- Form -->
                <form v-else @submit.prevent="handleSubmit" class="space-y-4">
                    <p class="text-sm text-gray-400 mb-2">
                        Reset password untuk: <strong class="text-gray-200">{{ routeEmail }}</strong>
                    </p>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Password Baru</label>
                        <div class="relative">
                            <i class="pi pi-lock absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm"></i>
                            <input
                                :type="showPassword ? 'text' : 'password'"
                                v-model="password"
                                placeholder="Min. 8 karakter"
                                class="input-field pl-10 pr-10 transition-all duration-200"
                                :class="{ 'border-orange-500/50 focus:border-orange-500': errors.password, 'focus:border-brand-500/50': !errors.password }"
                                :disabled="isLoading"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300 transition-colors"
                                tabindex="-1"
                            >
                                <i :class="showPassword ? 'pi pi-eye-slash' : 'pi pi-eye'" class="text-sm"></i>
                            </button>
                        </div>
                        <Transition name="slide-down">
                            <small v-if="errors.password" class="text-orange-400 flex items-center gap-1 mt-1">
                                <i class="pi pi-exclamation-circle text-xs"></i> {{ errors.password }}
                            </small>
                        </Transition>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Konfirmasi Password Baru</label>
                        <div class="relative">
                            <i class="pi pi-lock-open absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm"></i>
                            <input
                                :type="showConfirmPassword ? 'text' : 'password'"
                                v-model="confirmPassword"
                                placeholder="Ulangi password"
                                class="input-field pl-10 pr-10 transition-all duration-200"
                                :class="{ 'border-orange-500/50 focus:border-orange-500': errors.confirmPassword, 'focus:border-brand-500/50': !errors.confirmPassword }"
                                :disabled="isLoading"
                            />
                            <button
                                type="button"
                                @click="showConfirmPassword = !showConfirmPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300 transition-colors"
                                tabindex="-1"
                            >
                                <i :class="showConfirmPassword ? 'pi pi-eye-slash' : 'pi pi-eye'" class="text-sm"></i>
                            </button>
                        </div>
                        <Transition name="slide-down">
                            <small v-if="errors.confirmPassword" class="text-orange-400 flex items-center gap-1 mt-1">
                                <i class="pi pi-exclamation-circle text-xs"></i> {{ errors.confirmPassword }}
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
                        <i v-else class="pi pi-check"></i>
                        {{ isLoading ? 'Memproses...' : 'Reset Password' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { passwordResetApi } from '@/services/api';

const route = useRoute();

const password = ref('');
const confirmPassword = ref('');
const error = ref(null);
const errors = ref({});
const isLoading = ref(false);
const success = ref(false);
const showPassword = ref(false);
const showConfirmPassword = ref(false);
const routeEmail = ref('');
const routeToken = ref('');

onMounted(() => {
    routeEmail.value = route.query.email || '';
    routeToken.value = route.query.token || '';

    if (!routeToken.value || !routeEmail.value) {
        error.value = 'Link reset password tidak valid. Silakan minta link baru.';
    }
});

async function handleSubmit() {
    errors.value = {};
    error.value = null;

    if (!password.value || password.value.length < 8) {
        errors.value.password = 'Password minimal 8 karakter';
        return;
    }
    if (!confirmPassword.value) {
        errors.value.confirmPassword = 'Konfirmasi password wajib diisi';
        return;
    }
    if (password.value !== confirmPassword.value) {
        errors.value.confirmPassword = 'Konfirmasi password tidak cocok';
        return;
    }

    isLoading.value = true;
    try {
        await passwordResetApi.reset({
            token: routeToken.value,
            email: routeEmail.value,
            password: password.value,
            password_confirmation: confirmPassword.value,
        });
        success.value = true;
    } catch (err) {
        error.value = err.response?.data?.message || 'Gagal mereset password. Token mungkin sudah kedaluwarsa.';
    } finally {
        isLoading.value = false;
    }
}
</script>
