<template>
    <div class="container-page">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">Kategori</h1>
        <p class="text-gray-600 mb-8">Jelajahi kampanye berdasarkan kategori</p>

        <div v-if="isLoading" class="flex justify-center py-12">
            <i class="pi pi-spin pi-spinner text-4xl text-blue-600"></i>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
                v-for="category in categories"
                :key="category.id"
                class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 hover:shadow-md transition-shadow cursor-pointer"
                @click="router.push(`/campaigns?category_id=${category.id}`)"
            >
                <h3 class="font-semibold text-gray-900 text-lg mb-1">{{ category.name }}</h3>
                <p class="text-sm text-gray-600">{{ category.campaigns_count || 0 }} kampanye</p>
            </div>
        </div>

        <div v-if="!isLoading && categories.length === 0" class="text-center py-16">
            <i class="pi pi-tags text-gray-300 text-6xl mb-4"></i>
            <p class="text-gray-500">Belum ada kategori</p>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { categoryApi } from '@/services/api';

const router = useRouter();
const categories = ref([]);
const isLoading = ref(true);

onMounted(async () => {
    try {
        const response = await categoryApi.getAll();
        categories.value = response.data.data;
    } catch {
        // ignore
    } finally {
        isLoading.value = false;
    }
});
</script>
