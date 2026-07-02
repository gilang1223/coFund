<template>
    <div class="min-h-screen flex items-center justify-center px-4 py-12">
        <div class="max-w-md w-full">
            <div class="text-center mb-8">
                <router-link to="/" class="inline-flex items-center space-x-2 mb-6">
                    <i class="pi pi-heart-fill text-blue-600 text-2xl"></i>
                    <span class="text-xl font-bold text-gray-900">CoFund</span>
                </router-link>
                <h1 class="text-2xl font-bold text-gray-900">Selamat Datang Kembali</h1>
                <p class="text-gray-600 mt-1">Masuk ke akun Anda</p>
            </div>

            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <form @submit.prevent="handleLogin" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <InputText
                            v-model="email"
                            type="email"
                            class="w-full"
                            placeholder="your@email.com"
                            :class="{ 'p-invalid': errors.email }"
                        />
                        <small v-if="errors.email" class="text-red-500">{{ errors.email }}</small>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <Password
                            v-model="password"
                            :feedback="false"
                            class="w-full"
                            placeholder="Min. 8 karakter"
                            :class="{ 'p-invalid': errors.password }"
                        />
                        <small v-if="errors.password" class="text-red-500">{{ errors.password }}</small>
                    </div>

                    <div v-if="error" class="bg-red-50 text-red-600 p-3 rounded-lg text-sm">
                        {{ error }}
                    </div>

                    <Button
                        type="submit"
                        label="Login"
                        icon="pi pi-sign-in"
                        class="w-full"
                        :loading="isLoading"
                    />
                </form>

                <div class="mt-6 text-center text-sm">
                    <span class="text-gray-500">Belum punya akun?</span>
                    <router-link to="/register" class="text-blue-600 font-medium hover:underline ml-1">
                        Daftar
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuth } from '@/composables/useAuth';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';

const router = useRouter();
const route = useRoute();
const { login, isLoading, error } = useAuth();

const email = ref('');
const password = ref('');
const errors = ref({});

async function handleLogin() {
    errors.value = {};
    if (!email.value) errors.value.email = 'Email wajib diisi';
    if (!password.value) errors.value.password = 'Password wajib diisi';
    if (Object.keys(errors.value).length) return;

    try {
        await login(email.value, password.value);
        const redirect = route.query.redirect || '/dashboard';
        router.push(redirect);
    } catch {
        // error is handled by composable
    }
}
</script>
