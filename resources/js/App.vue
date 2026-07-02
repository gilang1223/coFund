<template>
    <router-view />
    <Toast position="bottom-right" />
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAppStore } from '@/stores/app';
import Toast from 'primevue/toast';

const router = useRouter();
const appStore = useAppStore();

function handleUnauthorized() {
    appStore.clearUser();
    router.push('/login');
}

onMounted(() => {
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
