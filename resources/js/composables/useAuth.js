import { ref } from 'vue';
import { authApi } from '@/services/api';
import { useAppStore } from '@/stores/app';

export function useAuth() {
    const isLoading = ref(false);
    const error = ref(null);

    async function login(email, password) {
        isLoading.value = true;
        error.value = null;
        try {
            const response = await authApi.login(email, password);
            const { token, user } = response.data.data;

            localStorage.setItem('auth_token', token);
            localStorage.setItem('auth_user', JSON.stringify(user));
            const appStore = useAppStore();
            appStore.setUser(user);

            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Login failed';
            throw err;
        } finally {
            isLoading.value = false;
        }
    }

    async function register(data) {
        isLoading.value = true;
        error.value = null;
        try {
            const response = await authApi.register(data);
            const { token, user } = response.data.data;

            localStorage.setItem('auth_token', token);
            localStorage.setItem('auth_user', JSON.stringify(user));
            const appStore = useAppStore();
            appStore.setUser(user);

            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Registration failed';
            throw err;
        } finally {
            isLoading.value = false;
        }
    }

    async function logout() {
        isLoading.value = true;
        try {
            await authApi.logout();
        } catch {
            // ignore
        } finally {
            const appStore = useAppStore();
            appStore.clearUser();
            isLoading.value = false;
        }
    }

    return {
        isLoading,
        error,
        login,
        register,
        logout,
    };
}
