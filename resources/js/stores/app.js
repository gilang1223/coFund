import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { authApi } from '@/services/api';

export const useAppStore = defineStore('app', () => {
    // State
    const user = ref(null);
    const isAuthenticated = ref(false);
    const isLoading = ref(false);
    const notification = ref(null);

    // Getters
    const isAdmin = computed(() => user.value?.role === 'admin');
    const isCreator = computed(() => user.value?.role === 'creator');
    const isBacker = computed(() => user.value?.role === 'backer');

    // Actions
    async function fetchUser() {
        try {
            const token = localStorage.getItem('auth_token');
            if (!token) {
                isAuthenticated.value = false;
                user.value = null;
                return;
            }
            const response = await authApi.getUser();
            user.value = response.data.data;
            isAuthenticated.value = true;
        } catch (error) {
            user.value = null;
            isAuthenticated.value = false;
            localStorage.removeItem('auth_token');
        }
    }

    function setUser(userData) {
        user.value = userData;
        isAuthenticated.value = true;
    }

    function clearUser() {
        user.value = null;
        isAuthenticated.value = false;
        localStorage.removeItem('auth_token');
    }

    function showNotification(message, type = 'success') {
        notification.value = { message, type };
        setTimeout(() => {
            notification.value = null;
        }, 5000);
    }

    return {
        user,
        isAuthenticated,
        isLoading,
        notification,
        isAdmin,
        isCreator,
        isBacker,
        fetchUser,
        setUser,
        clearUser,
        showNotification,
    };
});
