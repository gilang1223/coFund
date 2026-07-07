<template>
    <div class="animate-fade-in">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-semibold text-white">All Campaigns</h2>
                <p class="text-gray-500 text-sm mt-0.5">Kelola semua kampanye di platform</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="relative">
                    <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm"></i>
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Cari..."
                        class="input-field pl-9 py-1.5 text-sm w-48"
                        @input="debouncedSearch"
                    />
                </div>
                <select
                    v-model="filterStatus"
                    class="input-field py-1.5 text-sm w-32"
                    @change="$emit('fetch', 1)"
                >
                    <option value="">All</option>
                    <option value="active">Active</option>
                    <option value="review">Review</option>
                    <option value="draft">Draft</option>
                    <option value="success">Success</option>
                    <option value="failed">Failed</option>
                </select>
                <button @click="$emit('refresh')" class="text-sm text-brand-400 hover:text-brand-300 transition-colors">
                    <i class="pi pi-refresh"></i>
                </button>
            </div>
        </div>

        <!-- Skeleton -->
        <div v-if="loading" class="space-y-2">
            <div v-for="i in 5" :key="i" class="card p-4">
                <div class="flex items-center gap-4">
                    <div class="skeleton h-10 w-10 rounded-md shrink-0"></div>
                    <div class="flex-1 skeleton h-4 w-48"></div>
                    <div class="skeleton h-4 w-20"></div>
                    <div class="skeleton h-4 w-24"></div>
                    <div class="skeleton h-6 w-16 rounded-sm"></div>
                </div>
            </div>
        </div>

        <!-- Empty -->
        <div v-else-if="!campaigns || campaigns.length === 0" class="card p-10 text-center">
            <div class="w-16 h-16 rounded-md bg-navy-800 flex items-center justify-center mx-auto mb-4">
                <i class="pi pi-inbox text-gray-600 text-2xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-300 mb-1">Tidak ada campaign</h3>
            <p class="text-gray-500 text-sm">Belum ada campaign yang dibuat di platform.</p>
        </div>

        <!-- Campaigns Table -->
        <div v-else class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-xs text-gray-500 uppercase tracking-wider border-b border-navy-700/50">
                        <th class="pb-3 font-medium">Campaign</th>
                        <th class="pb-3 font-medium">Creator</th>
                        <th class="pb-3 font-medium">Target</th>
                        <th class="pb-3 font-medium">Collected</th>
                        <th class="pb-3 font-medium">Status</th>
                        <th class="pb-3 font-medium">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-navy-800/50">
                    <tr
                        v-for="c in campaigns"
                        :key="c.id"
                        class="hover:bg-navy-800/30 transition-colors"
                    >
                        <td class="py-3 pr-4">
                            <router-link :to="`/campaigns/${c.id}`" class="text-sm text-white font-medium hover:text-brand-400 transition-colors">
                                {{ c.title }}
                            </router-link>
                        </td>
                        <td class="py-3 pr-4">
                            <span class="text-sm text-gray-400">{{ c.creator?.name || '-' }}</span>
                        </td>
                        <td class="py-3 pr-4">
                            <span class="text-sm text-gray-300">{{ formatCurrency(c.target_amount) }}</span>
                        </td>
                        <td class="py-3 pr-4">
                            <span class="text-sm text-gray-300">{{ formatCurrency(c.collected_amount) }}</span>
                        </td>
                        <td class="py-3 pr-4">
                            <span
                                :class="[
                                    'text-xs font-medium px-2 py-0.5 rounded-sm',
                                    c.status === 'active' ? 'bg-green-500/10 text-green-400' :
                                    c.status === 'review' ? 'bg-orange-500/10 text-orange-400' :
                                    c.status === 'success' ? 'bg-brand-500/10 text-brand-400' :
                                    c.status === 'draft' ? 'bg-gray-500/10 text-gray-400' :
                                    'bg-red-500/10 text-red-400'
                                ]"
                            >{{ c.status }}</span>
                        </td>
                        <td class="py-3">
                            <span class="text-sm text-gray-500">{{ formatDate(c.created_at) }}</span>
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
import { ref } from 'vue';
import { useCampaign } from '@/composables/useCampaign';
import dayjs from '@/plugins/dayjs';

const props = defineProps({
    loading: Boolean,
    campaigns: Array,
    meta: Object,
    error: String,
});

const emit = defineEmits(['fetch', 'refresh']);

const { formatCurrency } = useCampaign();
const searchQuery = ref('');
const filterStatus = ref('');
let debounceTimer = null;

function debouncedSearch() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        emit('fetch', 1);
    }, 400);
}

function formatDate(date) {
    if (!date) return '-';
    return dayjs(date).format('D MMM YYYY');
}

function goToPage(page) {
    emit('fetch', page);
}
</script>
