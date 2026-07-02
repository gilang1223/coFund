import { ref } from 'vue';
import { backingApi, campaignApi } from '@/services/api';

export function useBacking() {
    const backings = ref([]);
    const myCampaigns = ref([]);
    const stats = ref(null);
    const isLoading = ref(false);
    const error = ref(null);
    const meta = ref(null);

    async function fetchMyBackings() {
        isLoading.value = true;
        error.value = null;
        try {
            const response = await backingApi.getMyBackings();
            backings.value = response.data.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch backings';
        } finally {
            isLoading.value = false;
        }
    }

    async function fetchMyCampaigns(params = {}) {
        isLoading.value = true;
        error.value = null;
        try {
            const response = await campaignApi.getMyCampaigns(params);
            myCampaigns.value = response.data.data;
            meta.value = response.data.meta;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch campaigns';
        } finally {
            isLoading.value = false;
        }
    }

    async function fetchDashboardStats() {
        isLoading.value = true;
        error.value = null;
        try {
            const response = await campaignApi.getDashboardStats();
            stats.value = response.data.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch stats';
        } finally {
            isLoading.value = false;
        }
    }

    async function createBacking(data) {
        isLoading.value = true;
        error.value = null;
        try {
            const response = await backingApi.create(data);
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to create backing';
            throw err;
        } finally {
            isLoading.value = false;
        }
    }

    function getStatusBadgeClass(status) {
        const map = {
            pending: 'badge-draft',
            completed: 'badge-success',
            refunded: 'badge-failed',
            failed: 'badge-failed',
        };
        return map[status] || 'badge-default';
    }

    return {
        backings,
        myCampaigns,
        stats,
        isLoading,
        error,
        meta,
        fetchMyBackings,
        fetchMyCampaigns,
        fetchDashboardStats,
        createBacking,
        getStatusBadgeClass,
    };
}
