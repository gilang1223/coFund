<template>
    <div class="min-h-screen flex items-center justify-center px-4 py-12 bg-navy-950 relative overflow-hidden">
        <!-- Background decorations -->
        <div class="absolute top-0 right-0 w-96 h-96 bg-orange-500/5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-orange-500/3 rounded-full blur-3xl"></div>

        <div class="max-w-md w-full animate-fade-in relative">
            <div class="text-center mb-8">
                <router-link to="/" class="inline-flex items-center space-x-2.5 mb-6 group">
                    <div class="w-10 h-10 rounded-md bg-orange-500/10 flex items-center justify-center group-hover:bg-orange-500/20 transition-colors">
                        <i class="pi pi-shield text-orange-400 text-lg"></i>
                    </div>
                    <span class="text-xl font-bold text-white">CoFund</span>
                    <span class="text-xs font-semibold text-orange-400 bg-orange-500/10 px-2 py-0.5 rounded-sm">Admin</span>
                </router-link>
                <h1 class="text-2xl font-bold text-white">Administrator Portal</h1>
                <p class="text-gray-500 mt-1">Masuk ke panel kontrol admin</p>
            </div>

            <Transition name="shake">
                <div :key="errorCount" class="card p-8 border-orange-500/10" :class="{ 'animate-shake': errorCount > 0 }">
                    <form @submit.prevent="handleLogin" class="space-y-4">
                        <!-- Email -->
                        <div class="transition-all duration-200" :class="{ 'opacity-80': isLoading }">
                            <label class="block text-sm font-medium text-gray-300 mb-1">Email</label>
                            <div class="relative">
                                <i class="pi pi-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm"></i>
                                <input
                                    v-model="email"
                                    type="email"
                                    placeholder="admin@cofund.com"
                                    class="input-field pl-10 transition-all duration-200 focus:border-orange-500/50"
                                    :class="{ 'border-orange-500/50 focus:border-orange-500': errors.email }"
                                    :disabled="isLoading"
                                />
                            </div>
                            <Transition name="slide-down">
                                <small v-if="errors.email" class="text-orange-400 flex items-center gap-1 mt-1">
                                    <i class="pi pi-exclamation-circle text-xs"></i> {{ errors.email }}
                                </small>
                            </Transition>
                        </div>

                        <!-- Password -->
                        <div class="transition-all duration-200" :class="{ 'opacity-80': isLoading }">
                            <label class="block text-sm font-medium text-gray-300 mb-1">Password</label>
                            <div class="relative">
                                <i class="pi pi-lock absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm"></i>
                                <input
                                    :type="showPassword ? 'text' : 'password'"
                                    v-model="password"
                                    placeholder="••••••••"
                                    class="input-field pl-10 pr-10 transition-all duration-200 focus:border-orange-500/50"
                                    :class="{ 'border-orange-500/50 focus:border-orange-500': errors.password }"
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
                            <div v-if="errorMsg" class="bg-orange-500/10 text-orange-400 p-3 rounded-md text-sm border border-orange-500/20 flex items-start gap-2">
                                <i class="pi pi-exclamation-triangle mt-0.5 shrink-0"></i>
                                <span>{{ errorMsg }}</span>
                            </div>
                        </Transition>

                        <button
                            type="submit"
                            :disabled="isLoading"
                            class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-orange-500 text-white font-medium rounded-md hover:bg-orange-600 transition-all duration-200 disabled:opacity-60 disabled:cursor-not-allowed shadow-lg shadow-orange-500/20 hover:shadow-orange-500/30"
                        >
                            <i v-if="isLoading" class="pi pi-spin pi-spinner"></i>
                            <i v-else class="pi pi-sign-in"></i>
                            {{ isLoading ? 'Memproses...' : 'Login Admin' }}
                        </button>
                    </form>
                </div>
            </Transition>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '@/composables/useAuth';
import { useAppStore } from '@/stores/app';
import { useForm, useField } from 'vee-validate';
import * as yup from 'yup';

const router = useRouter();
const appStore = useAppStore();
const { login, logout, isLoading, error } = useAuth();

const schema = yup.object({
    email: yup.string().required('Email wajib diisi').email('Format email tidak valid'),
    password: yup.string().required('Password wajib diisi'),
});

const { handleSubmit, errors } = useForm({
    validationSchema: schema,
});

const { value: email } = useField('email');
const { value: password } = useField('password');

const showPassword = ref(false);
const errorCount = ref(0);
const errorMsg = ref('');

const handleLogin = handleSubmit(async (values) => {
    errorMsg.value = '';
    try {
        await login(values.email, values.password);
        if (appStore.user?.role !== 'admin') {
            await logout();
            errorMsg.value = 'Akses ditolak. Hanya administrator yang diizinkan masuk ke panel ini.';
            errorCount.value++;
            return;
        }
        router.push('/admin');
    } catch (err) {
        errorCount.value++;
        errorMsg.value = error.value || 'Gagal masuk. Cek email dan password Anda.';
    }
});
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
