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
    addUpdate(id, data) {
        return apiClient.post(`/campaigns/${id}/updates`, data);
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
    getCreatorRequests(params = {}) {
        return apiClient.get('/admin/creator-requests', { params });
    },
    approveCreatorRequest(id) {
        return apiClient.post(`/admin/creator-requests/${id}/approve`);
    },
    rejectCreatorRequest(id, adminNote) {
        return apiClient.post(`/admin/creator-requests/${id}/reject`, { admin_note: adminNote });
    },
    suspendUser(id) {
        return apiClient.post(`/admin/users/${id}/suspend`);
    },
    reactivateUser(id) {
        return apiClient.post(`/admin/users/${id}/reactivate`);
    },
    getUserTransactions(id) {
        return apiClient.get(`/admin/users/${id}/transactions`);
    },
};

// Creator Request User API
export const creatorRequestApi = {
    submit(data) {
        return apiClient.post('/creator-requests', data);
    },
    getMyRequests() {
        return apiClient.get('/creator-requests/my');
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

// Password Reset API
export const passwordResetApi = {
    sendResetLink(email) {
        return apiClient.post('/forgot-password', { email });
    },
    reset(data) {
        return apiClient.post('/reset-password', data);
    },
};

// Support Messages API
export const supportApi = {
    getMessages() {
        return apiClient.get('/support-messages');
    },
    sendMessage(message) {
        return apiClient.post('/support-messages', { message });
    },
    getAdminConversations() {
        return apiClient.get('/admin/support-conversations');
    },
    getAdminConversation(userId) {
        return apiClient.get(`/admin/support-conversations/${userId}`);
    },
    adminReply(userId, message) {
        return apiClient.post(`/admin/support-conversations/${userId}/reply`, { message });
    },
};

// Withdrawal API
export const withdrawalApi = {
    create(data) {
        return apiClient.post('/withdrawals', data);
    },
    getAll() {
        return apiClient.get('/withdrawals');
    },
};

// Admin Withdrawal API
export const adminWithdrawalApi = {
    getAll(params = {}) {
        return apiClient.get('/admin/withdrawals', { params });
    },
    process(id) {
        return apiClient.post(`/admin/withdrawals/${id}/process`);
    },
    reject(id, adminNote) {
        return apiClient.post(`/admin/withdrawals/${id}/reject`, { admin_note: adminNote });
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
    sendVerificationEmail() {
        return apiClient.post('/email/verification-notification');
    },
    getVerificationStatus() {
        return apiClient.get('/email/verification-status');
    },
    deleteAccount() {
        return apiClient.delete('/user/delete-account');
    },
};
