<template>
    <router-view />
    <Toast position="bottom-right" />
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue';
import { useAppStore } from '@/stores/app';
import Toast from 'primevue/toast';

const appStore = useAppStore();

function handleUnauthorized() {
    appStore.clearUser();
}

onMounted(() => {
    // Clean up old dual-token keys (from previous admin_auth_token system)
    localStorage.removeItem('admin_auth_token');
    localStorage.removeItem('admin_auth_user');

    appStore.fetchUser();
    window.addEventListener('auth:unauthorized', handleUnauthorized);
});

onUnmounted(() => {
    window.removeEventListener('auth:unauthorized', handleUnauthorized);
});
</script>

<style>
#app {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    background-color: #1A1A2E;
}
</style>
