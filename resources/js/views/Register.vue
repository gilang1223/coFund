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
                <h1 class="text-2xl font-bold text-white">Buat Akun Baru</h1>
                <p class="text-gray-500 mt-1">Bergabung dengan komunitas CoFund</p>
            </div>

            <div class="card p-8">
                <form @submit.prevent="handleRegister" class="space-y-4">
                    <!-- Name -->
                    <div class="transition-all duration-200" :class="{ 'opacity-80': isLoading }">
                        <label class="block text-sm font-medium text-gray-300 mb-1">Nama Lengkap</label>
                        <div class="relative">
                            <i class="pi pi-user absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm"></i>
                            <input
                                v-model="name"
                                type="text"
                                placeholder="Nama Anda"
                                class="input-field pl-10 transition-all duration-200"
                                :class="{ 'border-orange-500/50 focus:border-orange-500': errors.name, 'focus:border-brand-500/50': !errors.name }"
                                :disabled="isLoading"
                            />
                        </div>
                        <Transition name="slide-down">
                            <small v-if="errors.name" class="text-orange-400 flex items-center gap-1 mt-1">
                                <i class="pi pi-exclamation-circle text-xs"></i> {{ errors.name }}
                            </small>
                        </Transition>
                    </div>

                    <!-- Email -->
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

                    <!-- Password -->
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
                                @input="updateStrength"
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

                        <!-- Password Strength Bar -->
                        <Transition name="slide-down">
                            <div v-if="password.length > 0" class="mt-2">
                                <div class="flex gap-1 mb-1">
                                    <div
                                        v-for="i in 4"
                                        :key="i"
                                        class="h-1 flex-1 rounded-sm transition-all duration-300"
                                        :class="i <= strengthLevel ? strengthColor : 'bg-navy-700'"
                                    ></div>
                                </div>
                                <p class="text-xs" :class="strengthTextClass">
                                    <i :class="strengthIcon" class="mr-1"></i>
                                    {{ strengthLabel }}
                                </p>
                            </div>
                        </Transition>

                        <Transition name="slide-down">
                            <small v-if="errors.password" class="text-orange-400 flex items-center gap-1 mt-1">
                                <i class="pi pi-exclamation-circle text-xs"></i> {{ errors.password }}
                            </small>
                        </Transition>
                    </div>

                    <!-- Role -->
                    <div class="transition-all duration-200" :class="{ 'opacity-80': isLoading }">
                        <label class="block text-sm font-medium text-gray-300 mb-2">Role</label>
                        <div class="flex gap-2">
                            <button
                                type="button"
                                @click="role = 'backer'"
                                :class="[
                                    'flex-1 px-4 py-2.5 rounded-md text-sm font-medium transition-all duration-200 border',
                                    role === 'backer'
                                        ? 'bg-blue-500/10 text-blue-400 border-blue-500/30 shadow-sm'
                                        : 'bg-navy-800 text-gray-500 border-navy-700 hover:text-gray-300 hover:border-navy-600'
                                ]"
                                :disabled="isLoading"
                            >
                                <i class="pi pi-heart mr-1.5"></i> Backer
                            </button>
                            <button
                                type="button"
                                @click="role = 'creator'"
                                :class="[
                                    'flex-1 px-4 py-2.5 rounded-md text-sm font-medium transition-all duration-200 border',
                                    role === 'creator'
                                        ? 'bg-brand-500/10 text-brand-400 border-brand-500/30 shadow-sm'
                                        : 'bg-navy-800 text-gray-500 border-navy-700 hover:text-gray-300 hover:border-navy-600'
                                ]"
                                :disabled="isLoading"
                            >
                                <i class="pi pi-megaphone mr-1.5"></i> Creator
                            </button>
                        </div>
                        <p class="text-xs text-gray-600 mt-1.5">
                            <template v-if="role === 'backer'">Donatur — dukung kampanye yang Anda percaya</template>
                            <template v-else>Pembuat — buat dan kelola kampanye Anda</template>
                        </p>
                    </div>

                    <!-- Error Alert -->
                    <Transition name="slide-down">
                        <div v-if="error" class="bg-orange-500/10 text-orange-400 p-3 rounded-md text-sm border border-orange-500/20 flex items-start gap-2">
                            <i class="pi pi-exclamation-triangle mt-0.5 shrink-0"></i>
                            <span>{{ error }}</span>
                        </div>
                    </Transition>

                    <!-- Submit -->
                    <button
                        type="submit"
                        :disabled="isLoading"
                        class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-brand-500 text-white font-medium rounded-md hover:bg-brand-600 transition-all duration-200 disabled:opacity-60 disabled:cursor-not-allowed shadow-lg shadow-brand-500/20 hover:shadow-brand-500/30"
                    >
                        <i v-if="isLoading" class="pi pi-spin pi-spinner"></i>
                        <i v-else class="pi pi-user-plus"></i>
                        {{ isLoading ? 'Memproses...' : 'Daftar' }}
                    </button>
                </form>

                <div class="mt-6 text-center text-sm">
                    <span class="text-gray-500">Sudah punya akun?</span>
                    <router-link to="/login" class="text-brand-400 font-medium hover:text-brand-300 transition-colors ml-1">
                        Login
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '@/composables/useAuth';

const router = useRouter();
const { register, isLoading, error } = useAuth();

const name = ref('');
const email = ref('');
const password = ref('');
const role = ref('backer');
const errors = ref({});
const showPassword = ref(false);
const strengthLevel = ref(0);

function updateStrength() {
    const pwd = password.value;
    let score = 0;
    if (pwd.length >= 8) score++;
    if (/[a-z]/.test(pwd) && /[A-Z]/.test(pwd)) score++;
    if (/\d/.test(pwd)) score++;
    if (/[^a-zA-Z0-9]/.test(pwd)) score++;
    strengthLevel.value = score;
}

const strengthColor = computed(() => {
    if (strengthLevel.value <= 1) return 'bg-red-500';
    if (strengthLevel.value === 2) return 'bg-orange-500';
    if (strengthLevel.value === 3) return 'bg-yellow-500';
    return 'bg-green-500';
});

const strengthLabel = computed(() => {
    if (strengthLevel.value <= 1) return 'Lemah';
    if (strengthLevel.value === 2) return 'Cukup';
    if (strengthLevel.value === 3) return 'Kuat';
    return 'Sangat Kuat';
});

const strengthTextClass = computed(() => {
    if (strengthLevel.value <= 1) return 'text-red-400';
    if (strengthLevel.value === 2) return 'text-orange-400';
    if (strengthLevel.value === 3) return 'text-yellow-400';
    return 'text-green-400';
});

const strengthIcon = computed(() => {
    if (strengthLevel.value <= 1) return 'pi pi-exclamation-circle';
    if (strengthLevel.value === 2) return 'pi pi-info-circle';
    if (strengthLevel.value === 3) return 'pi pi-check-circle';
    return 'pi pi-verified';
});

async function handleRegister() {
    errors.value = {};
    let hasError = false;
    if (!name.value) { errors.value.name = 'Nama wajib diisi'; hasError = true; }
    if (!email.value) { errors.value.email = 'Email wajib diisi'; hasError = true; }
    if (!password.value || password.value.length < 8) { errors.value.password = 'Password minimal 8 karakter'; hasError = true; }
    if (hasError) return;

    try {
        await register({ name: name.value, email: email.value, password: password.value, role: role.value });
        router.push('/dashboard');
    } catch {
        // error is handled by composable
    }
}
</script>
