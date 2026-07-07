<template>
    <div class="container-page max-w-3xl mx-auto animate-fade-in">
        <div class="mb-8">
            <h1 class="text-2xl md:text-3xl font-bold text-white">Buat Kampanye Baru</h1>
            <p class="text-gray-500 mt-1">Mulai penggalangan dana untuk idemu</p>
        </div>

        <div class="card p-8">
            <form @submit.prevent="handleCreate" class="space-y-6">
                <!-- Title -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Judul Kampanye <span class="text-brand-500">*</span></label>
                    <InputText
                        v-model="form.title"
                        class="w-full"
                        placeholder="Contoh: Bantu Pembangunan Perpustakaan Desa"
                        :class="{ 'p-invalid': errors.title }"
                    />
                    <small v-if="errors.title" class="text-orange-400">{{ errors.title }}</small>
                </div>

                <!-- Category -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Kategori <span class="text-brand-500">*</span></label>
                    <Dropdown
                        v-model="form.category_id"
                        :options="categories"
                        optionLabel="name"
                        optionValue="id"
                        placeholder="Pilih kategori"
                        class="w-full"
                        :class="{ 'p-invalid': errors.category_id }"
                    />
                    <small v-if="errors.category_id" class="text-orange-400">{{ errors.category_id }}</small>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Deskripsi <span class="text-brand-500">*</span></label>
                    <Textarea
                        v-model="form.description"
                        class="w-full"
                        rows="6"
                        placeholder="Ceritakan tentang kampanye Anda secara detail..."
                        :class="{ 'p-invalid': errors.description }"
                    />
                    <small v-if="errors.description" class="text-orange-400">{{ errors.description }}</small>
                </div>

                <!-- Target Amount -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Target Donasi <span class="text-brand-500">*</span></label>
                    <InputNumber
                        v-model="form.target_amount"
                        class="w-full"
                        placeholder="Min. Rp 100.000"
                        mode="currency"
                        currency="IDR"
                        locale="id-ID"
                        :min="100000"
                        :class="{ 'p-invalid': errors.target_amount }"
                    />
                    <small v-if="errors.target_amount" class="text-orange-400">{{ errors.target_amount }}</small>
                </div>

                <!-- Deadline -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Batas Waktu <span class="text-brand-500">*</span></label>
                    <Calendar
                        v-model="form.deadline"
                        :minDate="minDate"
                        dateFormat="dd/mm/yy"
                        class="w-full"
                        placeholder="Min. H+7 dari sekarang"
                        :class="{ 'p-invalid': errors.deadline }"
                    />
                    <small v-if="errors.deadline" class="text-orange-400">{{ errors.deadline }}</small>
                </div>

                <!-- Video URL -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">URL Video YouTube <span class="text-brand-500">*</span></label>
                    <InputText
                        v-model="form.video_url"
                        class="w-full"
                        placeholder="https://www.youtube.com/watch?v=..."
                        @input="updateThumbnailPreview"
                        :class="{ 'p-invalid': errors.video_url }"
                    />
                    <small v-if="errors.video_url" class="text-orange-400">{{ errors.video_url }}</small>
                    <small v-else class="text-gray-500 text-xs mt-1 block">Thumbnail akan diambil otomatis dari YouTube</small>
                    <div v-if="thumbnailPreview" class="mt-2 rounded-md overflow-hidden border border-navy-700 w-48">
                        <img :src="thumbnailPreview" alt="YouTube Thumbnail Preview" class="w-full h-auto" @error="thumbnailPreview = ''" />
                    </div>
                </div>

                <!-- Tiers -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-medium text-gray-300">Reward Tiers (Opsional)</label>
                        <Button
                            type="button"
                            icon="pi pi-plus"
                            label="Tambah Tier"
                            size="small"
                            severity="secondary"
                            @click="addTier"
                        />
                    </div>
                    <div v-for="(tier, index) in form.tiers" :key="index" class="card p-4 mb-3">
                        <div class="flex justify-between items-start mb-3">
                            <span class="text-sm font-medium text-gray-300">Tier {{ index + 1 }}</span>
                            <Button
                                type="button"
                                icon="pi pi-trash"
                                severity="danger"
                                size="small"
                                text
                                @click="removeTier(index)"
                            />
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Nama</label>
                                <InputText v-model="tier.name" class="w-full" placeholder="Contoh: Bronze" />
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Min. Donasi</label>
                                <InputNumber v-model="tier.min_amount" class="w-full" :min="10000" mode="currency" currency="IDR" locale="id-ID" />
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Kuota</label>
                                <InputNumber v-model="tier.quota" class="w-full" :min="1" />
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Deskripsi Reward</label>
                                <InputText v-model="tier.reward_description" class="w-full" placeholder="Deskripsi reward" />
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="error" class="bg-orange-500/10 text-orange-400 p-3 rounded-md text-sm border border-orange-500/20">
                    {{ error }}
                </div>

                <div class="flex gap-3">
                    <Button
                        type="submit"
                        label="Ajukan Kampanye"
                        icon="pi pi-send"
                        class="flex-1"
                        :loading="isLoading"
                    />
                    <Button
                        type="button"
                        label="Batal"
                        severity="secondary"
                        icon="pi pi-times"
                        @click="cancel"
                    />
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useCampaign } from '@/composables/useCampaign';
import { categoryApi } from '@/services/api';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import Dropdown from 'primevue/dropdown';
import Calendar from 'primevue/calendar';
import Button from 'primevue/button';
import dayjs from 'dayjs';

const router = useRouter();
const { createCampaign, isLoading, error } = useCampaign();

const categories = ref([]);
const errors = ref({});
const minDate = ref(dayjs().add(7, 'day').toDate());

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

const form = ref({
    title: '',
    category_id: null,
    description: '',
    target_amount: null,
    deadline: null,
    video_url: '',
    tiers: [],
});

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

function cancel() {
    router.push('/campaigns');
}

async function handleCreate() {
    errors.value = {};

    if (!form.value.title) errors.value.title = 'Judul wajib diisi';
    if (!form.value.category_id) errors.value.category_id = 'Kategori wajib dipilih';
    if (!form.value.description) errors.value.description = 'Deskripsi wajib diisi';
    if (!form.value.target_amount || form.value.target_amount < 100000) errors.value.target_amount = 'Minimal target Rp 100.000';
    if (!form.value.deadline) errors.value.deadline = 'Batas waktu wajib diisi';
    if (!form.value.video_url) errors.value.video_url = 'URL video YouTube wajib diisi';

    if (Object.keys(errors.value).length) return;

    try {
        const data = {
            ...form.value,
            deadline: dayjs(form.value.deadline).format('YYYY-MM-DD'),
        };

        await createCampaign(data);
        router.push('/campaigns');
    } catch {
        // error handled by composable
    }
}

onMounted(async () => {
    try {
        const response = await categoryApi.getAll();
        categories.value = response.data.data;
    } catch {
        // ignore
    }
});
</script>
