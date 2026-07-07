<template>
    <div class="container-page animate-fade-in">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-white">Kampanye Saya</h1>
                <p class="text-gray-500 mt-1">Kelola kampanye yang Anda buat</p>
            </div>
            <router-link
                to="/campaigns/create"
                class="btn-brand inline-flex"
            >
                <i class="pi pi-plus mr-2 text-xs"></i>
                Campaign Baru
            </router-link>
        </div>

        <!-- Skeleton Loading -->
        <div v-if="isLoading" class="space-y-4">
            <div v-for="i in 3" :key="i" class="card p-5">
                <div class="flex gap-4">
                    <div class="skeleton h-20 w-20 rounded-md shrink-0"></div>
                    <div class="flex-1 space-y-2">
                        <div class="skeleton-title"></div>
                        <div class="skeleton-text w-2/3"></div>
                        <div class="skeleton-text w-1/3"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else-if="myCampaigns.length === 0" class="text-center py-20">
            <div class="w-16 h-16 rounded-md bg-navy-800 flex items-center justify-center mx-auto mb-4">
                <i class="pi pi-verified text-gray-600 text-2xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-300 mb-2">Belum ada kampanye</h3>
            <p class="text-gray-500 mb-6">Anda belum membuat kampanye apapun.</p>
            <router-link to="/campaigns/create" class="btn-brand inline-flex">
                <i class="pi pi-plus-circle mr-2"></i>
                Buat Kampanye
            </router-link>
        </div>

        <!-- Campaign List -->
        <div v-else class="space-y-4">
            <div
                v-for="campaign in myCampaigns"
                :key="campaign.id"
                class="card p-5 hover:border-navy-600 transition-all duration-200"
            >
                <div class="flex flex-col sm:flex-row gap-4">
                    <!-- Thumbnail -->
                    <router-link :to="`/campaigns/${campaign.id}`" class="shrink-0">
                        <div class="w-full sm:w-24 h-24 bg-gradient-to-br from-brand-500/20 to-navy-700 rounded-md overflow-hidden flex items-center justify-center text-navy-600">
                            <img v-if="campaign.primary_image?.url && !brokenImages.has(campaign.id)" :src="campaign.primary_image.url" :alt="campaign.title" class="w-full h-full object-cover" @error="brokenImages.add(campaign.id)" />
                            <i v-else class="pi pi-image text-3xl"></i>
                        </div>
                    </router-link>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <router-link
                                    :to="`/campaigns/${campaign.id}`"
                                    class="text-sm font-semibold text-white hover:text-brand-400 transition-colors line-clamp-1"
                                >
                                    {{ campaign.title }}
                                </router-link>
                                <p class="text-xs text-gray-500 mt-1 line-clamp-1">
                                    {{ campaign.description }}
                                </p>
                            </div>
                            <div class="shrink-0 text-right">
                                <span
                                    :class="[
                                        campaign.status === 'active' ? 'badge-active' :
                                        campaign.status === 'success' ? 'badge-success' :
                                        campaign.status === 'review' ? 'badge-draft' :
                                        campaign.status === 'draft' ? 'badge-draft' :
                                        campaign.status === 'failed' ? 'badge-failed' :
                                        'badge-default'
                                    ]"
                                >
                                    {{ campaign.status }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-3 space-y-2">
                            <div class="progress-bar h-1.5">
                                <div
                                    class="progress-fill h-1.5"
                                    :style="{ width: `${getProgress(campaign.collected_amount, campaign.target_amount)}%` }"
                                ></div>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-400">
                                    {{ formatCurrency(campaign.collected_amount) }}
                                    dari {{ formatCurrency(campaign.target_amount) }}
                                </span>
                                <span class="text-gray-600">
                                    {{ getDaysRemaining(campaign.deadline) }} hari tersisa
                                </span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-2 mt-3">
                            <router-link
                                :to="`/campaigns/${campaign.id}`"
                                class="text-xs text-gray-400 hover:text-brand-400 transition-colors"
                            >
                                <i class="pi pi-eye mr-1"></i>Lihat
                            </router-link>
                            <button
                                v-if="campaign.status === 'draft'"
                                @click="submitForReview(campaign.id)"
                                class="text-xs text-gray-400 hover:text-brand-400 transition-colors"
                            >
                                <i class="pi pi-send mr-1"></i>Ajukan Review
                            </button>
                            <router-link
                                v-if="campaign.status === 'draft'"
                                :to="`/campaigns/${campaign.id}/edit`"
                                class="text-xs text-gray-400 hover:text-brand-400 transition-colors"
                            >
                                <i class="pi pi-pencil mr-1"></i>Edit
                            </router-link>
                            <button
                                v-if="campaign.status === 'draft'"
                                @click="confirmDelete(campaign)"
                                class="text-xs text-gray-400 hover:text-red-400 transition-colors"
                            >
                                <i class="pi pi-trash mr-1"></i>Hapus
                            </button>
                        </div>

                        <!-- Delete Confirmation Modal -->
                        <Transition name="fade">
                            <div v-if="deleteModal.show && deleteModal.campaign?.id === campaign.id" class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-black/60 backdrop-blur-sm" @click.self="closeDeleteModal">
                                <div class="card w-full max-w-sm p-6">
                                    <div class="text-center mb-4">
                                        <div class="w-14 h-14 rounded-full bg-red-500/10 flex items-center justify-center mx-auto mb-3">
                                            <i class="pi pi-exclamation-triangle text-red-400 text-2xl"></i>
                                        </div>
                                        <h3 class="text-lg font-semibold text-white mb-1">Hapus Kampanye</h3>
                                        <p class="text-sm text-gray-400">Apakah Anda yakin ingin menghapus kampanye <strong class="text-gray-300">"{{ deleteModal.campaign.title }}"</strong>? Tindakan ini tidak dapat dibatalkan.</p>
                                    </div>
                                    <div class="flex gap-3">
                                        <button
                                            @click="closeDeleteModal"
                                            class="flex-1 px-4 py-2.5 rounded-md text-sm font-medium text-gray-400 hover:text-white hover:bg-navy-700 border border-navy-700 transition-all"
                                        >
                                            Batal
                                        </button>
                                        <button
                                            @click="handleDelete"
                                            :disabled="deleteModal.isDeleting"
                                            class="flex-1 px-4 py-2.5 rounded-md text-sm font-medium bg-red-500/10 text-red-400 hover:bg-red-500/20 border border-red-500/20 transition-all disabled:opacity-50"
                                        >
                                            <i v-if="deleteModal.isDeleting" class="pi pi-spin pi-spinner mr-1"></i>
                                            <i v-else class="pi pi-trash mr-1"></i>
                                            Hapus
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="meta && meta.last_page > 1" class="flex justify-center mt-8">
                <div class="flex space-x-2">
                    <button
                        v-for="page in meta.last_page"
                        :key="page"
                        @click="fetchMyCampaigns({ page })"
                        :class="[
                            'px-3.5 py-2 rounded-md text-sm font-medium transition-all duration-200',
                            page === meta.current_page
                                ? 'bg-brand-500 text-white'
                                : 'bg-navy-800 text-gray-400 hover:text-white hover:bg-navy-700 border border-navy-700'
                        ]"
                    >
                        {{ page }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const brokenImages = ref(new Set());
import { useCampaign } from '@/composables/useCampaign';
import { useBacking } from '@/composables/useBacking';
import { campaignApi } from '@/services/api';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const { formatCurrency, getProgress, getDaysRemaining } = useCampaign();
const { myCampaigns, isLoading, meta, fetchMyCampaigns } = useBacking();

// Delete modal state
const deleteModal = ref({
    show: false,
    campaign: null,
    isDeleting: false,
});

function confirmDelete(campaign) {
    deleteModal.value = { show: true, campaign, isDeleting: false };
}

function closeDeleteModal() {
    deleteModal.value = { show: false, campaign: null, isDeleting: false };
}

async function handleDelete() {
    if (!deleteModal.value.campaign) return;
    deleteModal.value.isDeleting = true;
    try {
        await campaignApi.delete(deleteModal.value.campaign.id);
        toast.add({
            severity: 'success',
            summary: 'Berhasil',
            detail: 'Kampanye berhasil dihapus.',
            life: 3000,
        });
        closeDeleteModal();
        await fetchMyCampaigns();
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Gagal',
            detail: error.response?.data?.message || 'Gagal menghapus campaign',
            life: 3000,
        });
        closeDeleteModal();
    }
}

async function submitForReview(id) {
    try {
        await campaignApi.submitForReview(id);
        toast.add({
            severity: 'success',
            summary: 'Sukses',
            detail: 'Kampanye berhasil diajukan untuk review!',
            life: 3000,
        });
        await fetchMyCampaigns();
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Gagal',
            detail: error.response?.data?.message || 'Gagal mengajukan review',
            life: 3000,
        });
    }
}

onMounted(() => {
    fetchMyCampaigns();
});
</script>
