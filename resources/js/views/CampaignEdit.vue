<template>
    <div class="container-page max-w-3xl mx-auto animate-fade-in">
        <div class="mb-8">
            <h1 class="text-2xl md:text-3xl font-bold text-white">Edit Kampanye</h1>
            <p class="text-gray-500 mt-1">Edit kampanye draft Anda</p>
        </div>

        <div class="card p-8">
            <form @submit.prevent="handleUpdate" class="space-y-6">
                <!-- Title -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Judul Kampanye <span class="text-brand-500">*</span></label>
                    <input
                        v-model="form.title"
                        class="input-field"
                        placeholder="Contoh: Bantu Pembangunan Perpustakaan Desa"
                        :class="{ 'border-orange-500/50': errors.title }"
                    />
                    <small v-if="errors.title" class="text-orange-400">{{ errors.title }}</small>
                </div>

                <!-- Category -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Kategori <span class="text-brand-500">*</span></label>
                    <select
                        v-model="form.category_id"
                        class="input-field"
                        :class="{ 'border-orange-500/50': errors.category_id }"
                    >
                        <option value="" disabled>Pilih kategori</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                    <small v-if="errors.category_id" class="text-orange-400">{{ errors.category_id }}</small>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Deskripsi <span class="text-brand-500">*</span></label>
                    <textarea
                        v-model="form.description"
                        class="input-field"
                        rows="6"
                        placeholder="Ceritakan tentang kampanye Anda secara detail..."
                        :class="{ 'border-orange-500/50': errors.description }"
                    ></textarea>
                    <small v-if="errors.description" class="text-orange-400">{{ errors.description }}</small>
                </div>

                <!-- Target Amount -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Target Donasi <span class="text-brand-500">*</span></label>
                    <input
                        v-model.number="form.target_amount"
                        type="number"
                        class="input-field"
                        placeholder="Min. Rp 100.000"
                        :class="{ 'border-orange-500/50': errors.target_amount }"
                        min="100000"
                    />
                    <small v-if="errors.target_amount" class="text-orange-400">{{ errors.target_amount }}</small>
                </div>

                <!-- Deadline -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Batas Waktu <span class="text-brand-500">*</span></label>
                    <input
                        v-model="form.deadline"
                        type="date"
                        class="input-field"
                        :min="minDate"
                        :class="{ 'border-orange-500/50': errors.deadline }"
                    />
                    <small v-if="errors.deadline" class="text-orange-400">{{ errors.deadline }}</small>
                </div>

                <!-- Video URL -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">URL Video YouTube <span class="text-brand-500">*</span></label>
                    <input
                        v-model="form.video_url"
                        type="url"
                        class="input-field"
                        :class="{ 'border-orange-500/50': errors.video_url }"
                        placeholder="https://www.youtube.com/watch?v=..."
                        @input="updateThumbnailPreview"
                    />
                    <small v-if="errors.video_url" class="text-orange-400">{{ errors.video_url }}</small>
                    <small v-else class="text-gray-500 text-xs mt-1 block">Thumbnail akan diambil otomatis dari YouTube</small>
                    <div v-if="thumbnailPreview" class="mt-2 w-48 rounded-md overflow-hidden border border-navy-700">
                        <img :src="thumbnailPreview" alt="YouTube Thumbnail Preview" class="w-full h-auto" @error="thumbnailPreview = ''" />
                    </div>
                </div>

                <!-- Tier Rewards -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-medium text-gray-300">Reward Tiers (Opsional)</label>
                        <button
                            type="button"
                            @click="addTier"
                            class="text-xs text-brand-400 hover:text-brand-300 transition-colors"
                        >
                            <i class="pi pi-plus mr-1"></i>Tambah Tier
                        </button>
                    </div>
                    <div v-for="(tier, index) in form.tiers" :key="index" class="card p-4 mb-3">
                        <div class="flex justify-between items-start mb-3">
                            <span class="text-sm font-medium text-gray-300">Tier {{ index + 1 }}</span>
                            <button
                                type="button"
                                @click="removeTier(index)"
                                class="text-red-400 hover:text-red-300 text-xs transition-colors"
                            >
                                <i class="pi pi-trash"></i>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Nama</label>
                                <input v-model="tier.name" class="input-field" placeholder="Contoh: Bronze" />
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Min. Donasi</label>
                                <input v-model.number="tier.min_amount" type="number" class="input-field" min="10000" placeholder="10000" />
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Kuota</label>
                                <input v-model.number="tier.quota" type="number" class="input-field" min="1" placeholder="10" />
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Deskripsi Reward</label>
                                <input v-model="tier.reward_description" class="input-field" placeholder="Deskripsi reward" />
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="error" class="bg-orange-500/10 text-orange-400 p-3 rounded-md text-sm border border-orange-500/20">
                    {{ error }}
                </div>

                <div class="flex gap-3">
                    <button
                        type="submit"
                        :disabled="isLoading"
                        class="btn-brand flex-1"
                    >
                        <i v-if="isLoading" class="pi pi-spin pi-spinner mr-2"></i>
                        <i v-else class="pi pi-save mr-2"></i>
                        Simpan Perubahan
                    </button>
                    <button
                        type="button"
                        @click="cancel"
                        class="btn-ghost"
                    >
                        <i class="pi pi-times mr-2"></i>
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { campaignApi, categoryApi } from '@/services/api';
import { useToast } from 'primevue/usetoast';
import dayjs from 'dayjs';

const router = useRouter();
const route = useRoute();
const toast = useToast();

const categories = ref([]);
const isLoading = ref(false);
const error = ref(null);
const errors = ref({});
const minDate = ref(dayjs().add(7, 'day').format('YYYY-MM-DD'));

const thumbnailPreview = ref('');

function extractYouTubeId(url) {
    if (!url) return null;
    const patterns = [
        /(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/,
        /youtube\.com\/shorts\/([a-zA-Z0-9_-]{11})/,
    ];
    for (const p of patterns) {
        const match = url.match(p);
        if (match) return match[1];
    }
    return null;
}

function updateThumbnailPreview() {
    const id = extractYouTubeId(form.value.video_url);
    thumbnailPreview.value = id ? `https://img.youtube.com/vi/${id}/maxresdefault.jpg` : '';
}

function addTier() {
    form.value.tiers.push({
        name: '',
        min_amount: 10000,
        quota: 10,
        reward_description: '',
    });
}

function removeTier(index) {
    form.value.tiers.splice(index, 1);
}

const form = ref({
    title: '',
    category_id: '',
    description: '',
    target_amount: null,
    deadline: '',
    video_url: '',
    tiers: [],
});

function cancel() {
    router.push('/my-campaigns');
}

async function fetchCampaign() {
    isLoading.value = true;
    try {
        const res = await campaignApi.getById(route.params.id);
        const c = res.data.data;
        form.value = {
            title: c.title,
            category_id: c.category_id,
            description: c.description,
            target_amount: c.target_amount,
            deadline: dayjs(c.deadline).format('YYYY-MM-DD'),
            video_url: c.video_url || '',
            tiers: (c.tiers || []).map(t => ({
                id: t.id,
                name: t.name,
                min_amount: t.min_amount,
                quota: t.quota,
                reward_description: t.reward_description || '',
            })),
        };
        // Set thumbnail preview if video_url exists
        updateThumbnailPreview();
    } catch (err) {
        error.value = 'Gagal memuat data campaign';
    } finally {
        isLoading.value = false;
    }
}

async function handleUpdate() {
    errors.value = {};
    error.value = null;

    if (!form.value.title) errors.value.title = 'Judul wajib diisi';
    if (!form.value.category_id) errors.value.category_id = 'Kategori wajib dipilih';
    if (!form.value.description) errors.value.description = 'Deskripsi wajib diisi';
    if (!form.value.target_amount || form.value.target_amount < 100000) errors.value.target_amount = 'Minimal target Rp 100.000';
    if (!form.value.deadline) errors.value.deadline = 'Batas waktu wajib diisi';
    if (!form.value.video_url) errors.value.video_url = 'URL video YouTube wajib diisi';

    if (Object.keys(errors.value).length) return;

    isLoading.value = true;
    try {
        await campaignApi.update(route.params.id, {
            ...form.value,
            deadline: dayjs(form.value.deadline).format('YYYY-MM-DD'),
        });
        toast.add({
            severity: 'success',
            summary: 'Sukses',
            detail: 'Kampanye berhasil diperbarui!',
            life: 3000,
        });
        router.push('/my-campaigns');
    } catch (err) {
        error.value = err.response?.data?.message || 'Gagal memperbarui campaign';
    } finally {
        isLoading.value = false;
    }
}

onMounted(async () => {
    try {
        const res = await categoryApi.getAll();
        categories.value = res.data.data;
    } catch {}
    await fetchCampaign();
});
</script>
