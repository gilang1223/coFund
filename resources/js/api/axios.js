import axios from 'axios';

const apiClient = axios.create({
    baseURL: '/api',
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
    },
});

// Request interceptor to attach token
apiClient.interceptors.request.use(
    (config) => {
        const isAdminPage = window.location.pathname.startsWith('/admin');
        const token = localStorage.getItem(isAdminPage ? 'admin_auth_token' : 'auth_token');
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
    },
    (error) => Promise.reject(error)
);

// Response interceptor for error handling
apiClient.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            const isAdminPage = window.location.pathname.startsWith('/admin');
            localStorage.removeItem(isAdminPage ? 'admin_auth_token' : 'auth_token');
            localStorage.removeItem(isAdminPage ? 'admin_auth_user' : 'auth_user');
            window.dispatchEvent(new CustomEvent('auth:unauthorized', { detail: { isAdmin: isAdminPage } }));
        }
        return Promise.reject(error);
    }
);

export default apiClient;
