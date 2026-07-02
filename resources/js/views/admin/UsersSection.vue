<template>
    <div class="animate-fade-in">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-semibold text-white">Manage Users</h2>
                <p class="text-gray-500 text-sm mt-0.5">Daftar seluruh pengguna platform</p>
            </div>
            <button @click="$emit('refresh')" class="text-sm text-brand-400 hover:text-brand-300 transition-colors">
                <i class="pi pi-refresh mr-1"></i> Refresh
            </button>
        </div>

        <!-- Skeleton -->
        <div v-if="loading" class="space-y-2">
            <div v-for="i in 5" :key="i" class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="skeleton h-10 w-10 rounded-full shrink-0"></div>
                    <div class="flex-1 space-y-1">
                        <div class="skeleton h-4 w-36"></div>
                        <div class="skeleton h-3 w-48"></div>
                    </div>
                    <div class="skeleton h-5 w-16 rounded-sm"></div>
                    <div class="skeleton h-4 w-20"></div>
                </div>
            </div>
        </div>

        <!-- Error -->
        <div v-else-if="error" class="text-center py-10">
            <div class="w-14 h-14 rounded-md bg-orange-500/10 flex items-center justify-center mx-auto mb-3">
                <i class="pi pi-exclamation-triangle text-orange-400 text-xl"></i>
            </div>
            <p class="text-orange-400 mb-3">{{ error }}</p>
            <button @click="$emit('refresh')" class="text-sm text-brand-400 hover:text-brand-300 transition-colors underline">Coba Lagi</button>
        </div>

        <!-- Empty -->
        <div v-else-if="!users || users.length === 0" class="card p-10 text-center">
            <div class="w-16 h-16 rounded-md bg-navy-800 flex items-center justify-center mx-auto mb-4">
                <i class="pi pi-users text-gray-600 text-2xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-300 mb-1">Tidak ada user</h3>
            <p class="text-gray-500 text-sm">Belum ada pengguna yang terdaftar.</p>
        </div>

        <!-- Users Table -->
        <div v-else class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-xs text-gray-500 uppercase tracking-wider border-b border-navy-700/50">
                        <th class="pb-3 font-medium">User</th>
                        <th class="pb-3 font-medium">Email</th>
                        <th class="pb-3 font-medium">Role</th>
                        <th class="pb-3 font-medium">Balance</th>
                        <th class="pb-3 font-medium">Joined</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-navy-800/50">
                    <tr
                        v-for="user in users"
                        :key="user.id"
                        class="hover:bg-navy-800/30 transition-colors"
                    >
                        <td class="py-3 pr-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold shrink-0"
                                    :class="user.role === 'admin' ? 'bg-orange-500/20 text-orange-400' : user.role === 'creator' ? 'bg-brand-500/20 text-brand-400' : 'bg-blue-500/20 text-blue-400'"
                                >
                                    {{ getInitials(user.name) }}
                                </div>
                                <span class="text-sm text-white font-medium">{{ user.name }}</span>
                            </div>
                        </td>
                        <td class="py-3 pr-4">
                            <span class="text-sm text-gray-400">{{ user.email }}</span>
                        </td>
                        <td class="py-3 pr-4">
                            <span
                                :class="[
                                    'text-xs font-medium px-2 py-0.5 rounded-sm',
                                    user.role === 'admin' ? 'bg-orange-500/10 text-orange-400' :
                                    user.role === 'creator' ? 'bg-brand-500/10 text-brand-400' :
                                    'bg-blue-500/10 text-blue-400'
                                ]"
                            >{{ user.role }}</span>
                        </td>
                        <td class="py-3 pr-4">
                            <span class="text-sm text-gray-300">{{ formatCurrency(user.balance || 0) }}</span>
                        </td>
                        <td class="py-3">
                            <span class="text-sm text-gray-500">{{ formatDate(user.created_at) }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="meta && meta.last_page > 1" class="flex justify-center pt-6">
                <div class="flex gap-2">
                    <button
                        v-for="p in meta.last_page"
                        :key="p"
                        @click="goToPage(p)"
                        :class="[
                            'px-3 py-1.5 rounded-md text-sm font-medium transition-all',
                            p === meta.current_page
                                ? 'bg-brand-500/20 text-brand-400'
                                : 'bg-navy-800 text-gray-500 hover:text-gray-300 border border-navy-700'
                        ]"
                    >{{ p }}</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useCampaign } from '@/composables/useCampaign';

const props = defineProps({
    loading: Boolean,
    users: Array,
    meta: Object,
    error: String,
});

const emit = defineEmits(['fetch', 'refresh']);

const { formatCurrency } = useCampaign();

function getInitials(name) {
    if (!name) return '?';
    return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
}

function formatDate(date) {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' });
}

function goToPage(page) {
    emit('fetch', page);
}
</script>
