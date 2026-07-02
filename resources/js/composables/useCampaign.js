import { ref, computed } from 'vue';
import { campaignApi } from '@/services/api';
import dayjs from 'dayjs';

export function useCampaign() {
    const campaigns = ref([]);
    const currentCampaign = ref(null);
    const isLoading = ref(false);
    const error = ref(null);
    const meta = ref(null);

    const activeCampaigns = computed(() =>
        campaigns.value.filter((c) => c.status === 'active')
    );

    const featuredCampaigns = computed(() =>
        activeCampaigns.value.slice(0, 4)
    );

    async function fetchCampaigns(params = {}) {
        isLoading.value = true;
        error.value = null;
        try {
            const response = await campaignApi.getAll(params);
            campaigns.value = response.data.data;
            meta.value = response.data.meta;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch campaigns';
        } finally {
            isLoading.value = false;
        }
    }

    async function fetchCampaign(id) {
        isLoading.value = true;
        error.value = null;
        try {
            const response = await campaignApi.getById(id);
            currentCampaign.value = response.data.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch campaign';
        } finally {
            isLoading.value = false;
        }
    }

    async function createCampaign(data) {
        isLoading.value = true;
        error.value = null;
        try {
            const response = await campaignApi.create(data);
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to create campaign';
            throw err;
        } finally {
            isLoading.value = false;
        }
    }

    function formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
        }).format(amount);
    }

    function formatDate(date) {
        return dayjs(date).format('DD MMM YYYY');
    }

    function getProgress(collected, target) {
        return Math.min(Math.round((collected / target) * 100), 100);
    }

    function getDaysRemaining(deadline) {
        const days = dayjs(deadline).diff(dayjs(), 'day');
        return days > 0 ? days : 0;
    }

    return {
        campaigns,
        currentCampaign,
        isLoading,
        error,
        meta,
        activeCampaigns,
        featuredCampaigns,
        fetchCampaigns,
        fetchCampaign,
        createCampaign,
        formatCurrency,
        formatDate,
        getProgress,
        getDaysRemaining,
    };
}
