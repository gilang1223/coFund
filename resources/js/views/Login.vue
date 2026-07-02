<template>
    <div class="min-h-screen flex items-center justify-center px-4 py-12 bg-navy-950 relative overflow-hidden">
        <!-- Background decorations -->
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
                <h1 class="text-2xl font-bold text-white">Selamat Datang Kembali</h1>
                <p class="text-gray-500 mt-1">Masuk ke akun Anda</p>
            </div>

            <Transition name="shake">
                <div :key="errorCount" class="card p-8" :class="{ 'animate-shake': errorCount > 0 }">
                    <form @submit.prevent="handleLogin" class="space-y-4">
                        <div class="transition-all duration-200" :class="{ 'opacity-80': isLoading }">
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

                        <div class="transition-all duration-200" :class="{ 'opacity-80': isLoading }">
                            <label class="block text-sm font-medium text-gray-300 mb-1">Password</label>
                            <div class="relative">
                                <i class="pi pi-lock absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm"></i>
                                <input
                                    :type="showPassword ? 'text' : 'password'"
                                    v-model="password"
                                    placeholder="Min. 8 karakter"
                                    class="input-field pl-10 pr-10 transition-all duration-200"
                                    :class="{ 'border-orange-500/50 focus:border-orange-500': errors.password, 'focus:border-brand-500/50': !errors.password }"
                                    :disabled="isLoading"
                                    @keydown.enter="handleLogin"
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
                            <i v-else class="pi pi-sign-in"></i>
                            {{ isLoading ? 'Memproses...' : 'Login' }}
                        </button>

                        <!-- Demo credentials hint -->
                        <div class="pt-2 border-t border-navy-700/30">
                            <p class="text-xs text-gray-600 text-center">
                                Demo: <span class="text-gray-500">backer@cofund.com / password123</span>
                            </p>
                        </div>
                    </form>

                    <div class="mt-6 text-center text-sm">
                        <span class="text-gray-500">Belum punya akun?</span>
                        <router-link to="/register" class="text-brand-400 font-medium hover:text-brand-300 transition-colors ml-1">
                            Daftar Sekarang
                        </router-link>
                    </div>
                </div>
            </Transition>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuth } from '@/composables/useAuth';

const router = useRouter();
const route = useRoute();
const { login, isLoading, error } = useAuth();

const email = ref('');
const password = ref('');
const errors = ref({});
const showPassword = ref(false);
const errorCount = ref(0);

async function handleLogin() {
    errors.value = {};
    let hasError = false;
    if (!email.value) { errors.value.email = 'Email wajib diisi'; hasError = true; }
    if (!password.value) { errors.value.password = 'Password wajib diisi'; hasError = true; }
    if (hasError) return;

    try {
        await login(email.value, password.value);
        const redirect = route.query.redirect || '/dashboard';
        router.push(redirect);
    } catch {
        errorCount.value++;
    }
}
</script>

<style scoped>
.animate-shake {
    animation: shake 0.4s ease-in-out;
}
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    20% { transform: translateX(-6px); }
    40% { transform: translateX(6px); }
    60% { transform: translateX(-4px); }
    80% { transform: translateX(4px); }
}
</style>
