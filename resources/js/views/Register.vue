<template>
    <div class="min-h-screen flex items-center justify-center px-4 py-12 bg-navy-950">
        <div class="max-w-md w-full animate-fade-in">
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
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Nama Lengkap</label>
                        <InputText
                            v-model="name"
                            class="w-full"
                            placeholder="Nama Anda"
                            :class="{ 'p-invalid': errors.name }"
                        />
                        <small v-if="errors.name" class="text-orange-400">{{ errors.name }}</small>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Email</label>
                        <InputText
                            v-model="email"
                            type="email"
                            class="w-full"
                            placeholder="your@email.com"
                            :class="{ 'p-invalid': errors.email }"
                        />
                        <small v-if="errors.email" class="text-orange-400">{{ errors.email }}</small>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Password</label>
                        <Password
                            v-model="password"
                            class="w-full"
                            placeholder="Min. 8 karakter"
                            :feedback="true"
                            :class="{ 'p-invalid': errors.password }"
                        />
                        <small v-if="errors.password" class="text-orange-400">{{ errors.password }}</small>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Role</label>
                        <SelectButton
                            v-model="role"
                            :options="roleOptions"
                            optionLabel="label"
                            optionValue="value"
                            class="w-full"
                        />
                    </div>

                    <div v-if="error" class="bg-orange-500/10 text-orange-400 p-3 rounded-md text-sm border border-orange-500/20">
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
