import apiClient from '@/api/axios';

// Campaign API
export const campaignApi = {
    getAll(params = {}) {
        return apiClient.get('/campaigns', { params });
    },
    getById(id) {
        return apiClient.get(`/campaigns/${id}`);
    },
    create(data) {
        return apiClient.post('/campaigns', data);
    },
    update(id, data) {
        return apiClient.put(`/campaigns/${id}`, data);
    },
    delete(id) {
        return apiClient.delete(`/campaigns/${id}`);
    },
    submitForReview(id) {
        return apiClient.post(`/campaigns/${id}/submit-review`);
    },
    approve(id) {
        return apiClient.post(`/campaigns/${id}/approve`);
    },
    reject(id) {
        return apiClient.post(`/campaigns/${id}/reject`);
    },
    getMyCampaigns(params = {}) {
        return apiClient.get('/my-campaigns', { params });
    },
    getDashboardStats() {
        return apiClient.get('/dashboard-stats');
    },
};

// Category API
export const categoryApi = {
    getAll() {
        return apiClient.get('/categories');
    },
    getById(id) {
        return apiClient.get(`/categories/${id}`);
    },
    create(data) {
        return apiClient.post('/categories', data);
    },
    update(id, data) {
        return apiClient.put(`/categories/${id}`, data);
    },
    delete(id) {
        return apiClient.delete(`/categories/${id}`);
    },
};

// Backing API
export const backingApi = {
    create(data) {
        return apiClient.post('/backings', data);
    },
    complete(id) {
        return apiClient.post(`/backings/${id}/complete`);
    },
    refund(id) {
        return apiClient.post(`/backings/${id}/refund`);
    },
    getMyBackings() {
        return apiClient.get('/my-backings');
    },
    getCampaignBackings(campaignId) {
        return apiClient.get(`/campaigns/${campaignId}/backings`);
    },
};

// Transaction API
export const transactionApi = {
    getAll() {
        return apiClient.get('/transactions');
    },
    getByReference(reference) {
        return apiClient.get(`/transactions/${reference}`);
    },
    completeBacking(id) {
        return apiClient.post(`/backings/${id}/complete`);
    },
    refundBacking(id) {
        return apiClient.post(`/backings/${id}/refund`);
    },
    disburseCampaign(campaignId) {
        return apiClient.post(`/campaigns/${campaignId}/disburse`);
    },
    refundAll(campaignId) {
        return apiClient.post(`/campaigns/${campaignId}/refund-all`);
    },
    settleCampaign(campaignId) {
        return apiClient.post(`/campaigns/${campaignId}/settle`);
    },
};

// Admin API
export const adminApi = {
    getOverview() {
        return apiClient.get('/admin/overview');
    },
    getUsers(params = {}) {
        return apiClient.get('/admin/users', { params });
    },
    getPendingReviews(params = {}) {
        return apiClient.get('/admin/pending-reviews', { params });
    },
    getAllCampaigns(params = {}) {
        return apiClient.get('/admin/campaigns', { params });
    },
};

// Notification API
export const notificationApi = {
    getAll(params = {}) {
        return apiClient.get('/notifications', { params });
    },
    getUnreadCount() {
        return apiClient.get('/notifications/unread-count');
    },
    markAsRead(id) {
        return apiClient.post(`/notifications/${id}/read`);
    },
    markAllAsRead() {
        return apiClient.post('/notifications/mark-all-read');
    },
    delete(id) {
        return apiClient.delete(`/notifications/${id}`);
    },
};

// Auth API
export const authApi = {
    login(email, password) {
        return apiClient.post('/login', { email, password });
    },
    register(data) {
        return apiClient.post('/register', data);
    },
    logout() {
        return apiClient.post('/logout');
    },
    getUser() {
        return apiClient.get('/user');
    },
};
