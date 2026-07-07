import { ref } from 'vue';
import { adminApi } from '@/services/api';

/**
 * Composable for admin panel operations.
 * Wraps adminApi to provide reactive state.
 */
export function useAdmin() {
    const isLoading = ref(false);
    const error = ref(null);

    async function fetchOverview() {
        isLoading.value = true;
        error.value = null;
        try {
            const res = await adminApi.getOverview();
            return res.data.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Gagal memuat overview';
            return null;
        } finally {
            isLoading.value = false;
        }
    }

    async function fetchUsers(params = {}) {
        try {
            const res = await adminApi.getUsers(params);
            return res.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Gagal memuat users';
            return null;
        }
    }

    async function fetchPendingReviews(params = {}) {
        try {
            const res = await adminApi.getPendingReviews(params);
            return res.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Gagal memuat pending reviews';
            return null;
        }
    }

    async function fetchAllCampaigns(params = {}) {
        try {
            const res = await adminApi.getAllCampaigns(params);
            return res.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Gagal memuat campaigns';
            return null;
        }
    }

    async function fetchCreatorRequests(params = {}) {
        try {
            const res = await adminApi.getCreatorRequests(params);
            return res.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Gagal memuat creator requests';
            return null;
        }
    }

    async function suspendUser(id) {
        try {
            await adminApi.suspendUser(id);
            return true;
        } catch (err) {
            throw err;
        }
    }

    async function reactivateUser(id) {
        try {
            await adminApi.reactivateUser(id);
            return true;
        } catch (err) {
            throw err;
        }
    }

    async function approveCreatorRequest(id) {
        try {
            await adminApi.approveCreatorRequest(id);
            return true;
        } catch (err) {
            throw err;
        }
    }

    async function rejectCreatorRequest(id, note) {
        try {
            await adminApi.rejectCreatorRequest(id, note);
            return true;
        } catch (err) {
            throw err;
        }
    }

    return {
        isLoading,
        error,
        fetchOverview,
        fetchUsers,
        fetchPendingReviews,
        fetchAllCampaigns,
        fetchCreatorRequests,
        suspendUser,
        reactivateUser,
        approveCreatorRequest,
        rejectCreatorRequest,
    };
}
