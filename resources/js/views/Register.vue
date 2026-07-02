<template>
    <div class="min-h-screen flex items-center justify-center px-4 py-12">
        <div class="max-w-md w-full">
            <div class="text-center mb-8">
                <router-link to="/" class="inline-flex items-center space-x-2 mb-6">
                    <i class="pi pi-heart-fill text-blue-600 text-2xl"></i>
                    <span class="text-xl font-bold text-gray-900">CoFund</span>
                </router-link>
                <h1 class="text-2xl font-bold text-gray-900">Buat Akun Baru</h1>
                <p class="text-gray-600 mt-1">Bergabung dengan komunitas CoFund</p>
            </div>

            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <form @submit.prevent="handleRegister" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                        <InputText
                            v-model="name"
                            class="w-full"
                            placeholder="Nama Anda"
                            :class="{ 'p-invalid': errors.name }"
                        />
                        <small v-if="errors.name" class="text-red-500">{{ errors.name }}</small>
                    </div>
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
                            class="w-full"
                            placeholder="Min. 8 karakter"
                            :feedback="true"
                            :class="{ 'p-invalid': errors.password }"
                        />
                        <small v-if="errors.password" class="text-red-500">{{ errors.password }}</small>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                        <SelectButton
                            v-model="role"
                            :options="roleOptions"
                            optionLabel="label"
                            optionValue="value"
                            class="w-full"
                        />
                    </div>

                    <div v-if="error" class="bg-red-50 text-red-600 p-3 rounded-lg text-sm">
                        {{ error }}
                    </div>

                    <Button
                        type="submit"
                        label="Daftar"
                        icon="pi pi-user-plus"
                        class="w-full"
                        :loading="isLoading"
                    />
                </form>

                <div class="mt-6 text-center text-sm">
                    <span class="text-gray-500">Sudah punya akun?</span>
                    <router-link to="/login" class="text-blue-600 font-medium hover:underline ml-1">
                        Login
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '@/composables/useAuth';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';
import SelectButton from 'primevue/selectbutton';

const router = useRouter();
const { register, isLoading, error } = useAuth();

const name = ref('');
const email = ref('');
const password = ref('');
const role = ref('backer');
const errors = ref({});

const roleOptions = [
    { label: 'Backer (Donatur)', value: 'backer' },
    { label: 'Creator (Pembuat)', value: 'creator' },
];

async function handleRegister() {
    errors.value = {};
    if (!name.value) errors.value.name = 'Nama wajib diisi';
    if (!email.value) errors.value.email = 'Email wajib diisi';
    if (!password.value || password.value.length < 8) errors.value.password = 'Password minimal 8 karakter';
    if (Object.keys(errors.value).length) return;

    try {
        await register({ name: name.value, email: email.value, password: password.value, role: role.value });
        router.push('/dashboard');
    } catch {
        // error is handled by composable
    }
}
</script>
