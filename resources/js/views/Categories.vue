<template>
    <div class="container-page animate-fade-in">
        <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">Kategori</h1>
        <p class="text-gray-500 mb-8">Jelajahi kampanye berdasarkan kategori</p>

        <!-- Skeleton Loading -->
        <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <div v-for="i in 6" :key="i" class="card p-6">
                <div class="skeleton-title mb-2"></div>
                <div class="skeleton-text w-1/2"></div>
            </div>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <div
                v-for="category in categories"
                :key="category.id"
                class="card p-6 hover:border-brand-500/30 hover:shadow-card transition-all duration-200 cursor-pointer group"
                @click="router.push(`/campaigns?category_id=${category.id}`)"
            >
                <h3 class="font-semibold text-white text-lg mb-1 group-hover:text-brand-400 transition-colors">{{ category.name }}</h3>
                <p class="text-sm text-gray-500">{{ category.campaigns_count || 0 }} kampanye</p>
            </div>
        </div>

        <div v-if="!isLoading && categories.length === 0" class="text-center py-16">
            <div class="w-16 h-16 rounded-md bg-navy-800 flex items-center justify-center mx-auto mb-4">
                <i class="pi pi-tags text-gray-600 text-2xl"></i>
            </div>
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
