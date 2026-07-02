<template>
    <div class="container-page animate-fade-in">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-white">Notifikasi</h1>
                <p class="text-gray-500 mt-1">Pemberitahuan dan aktivitas terbaru</p>
            </div>
            <button
                v-if="hasUnread"
                @click="markAllAsRead"
                class="btn-ghost text-sm px-4 py-2 rounded-md border border-navy-700 hover:border-brand-500/30 transition-all"
            >
                <i class="pi pi-check-double mr-2"></i>
                Tandai Semua Terbaca
            </button>
        </div>

        <!-- Skeleton -->
        <div v-if="loading" class="space-y-3">
            <div v-for="i in 5" :key="i" class="card p-5">
                <div class="flex gap-4">
                    <div class="skeleton h-10 w-10 rounded-full shrink-0"></div>
                    <div class="flex-1">
                        <div class="skeleton h-5 w-48 mb-2"></div>
                        <div class="skeleton h-4 w-full mb-1"></div>
                        <div class="skeleton h-3 w-24"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else-if="notifications.length === 0" class="text-center py-20">
            <div class="w-16 h-16 rounded-full bg-navy-800/50 flex items-center justify-center mx-auto mb-4">
                <i class="pi pi-bell text-gray-600 text-2xl"></i>
            </div>
            <p class="text-gray-500 text-lg">Belum ada notifikasi</p>
            <p class="text-gray-600 text-sm mt-1">Notifikasi akan muncul saat ada aktivitas terkait kampanye Anda</p>
        </div>

        <!-- Notification List -->
        <div v-else class="space-y-2">
            <div
                v-for="notification in notifications"
                :key="notification.id"
                class="card p-5 transition-all duration-200 cursor-pointer"
                :class="[
                    notification.read_at
                        ? 'opacity-70 hover:opacity-100'
                        : 'border-brand-500/20 bg-navy-700/20'
                ]"
                @click="markAsRead(notification)"
            >
                <div class="flex gap-4 items-start">
                    <!-- Icon -->
                    <div
                        class="w-10 h-10 rounded-full flex items-center justify-center shrink-0"
                        :class="getIconClass(notification.type)"
                    >
                        <i :class="[getIcon(notification.type), 'text-lg']"></i>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p
                                    class="text-sm font-semibold"
                                    :class="notification.read_at ? 'text-gray-300' : 'text-white'"
                                >
                                    {{ notification.title }}
                                </p>
                                <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ notification.body }}</p>
                            </div>
                            <!-- Unread dot -->
                            <div v-if="!notification.read_at" class="w-2 h-2 rounded-full bg-brand-500 shrink-0 mt-2"></div>
                        </div>
                        <p class="text-xs text-gray-600 mt-2">{{ formatDate(notification.created_at) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { notificationApi } from '@/services/api';

const notifications = ref([]);
const loading = ref(true);
const hasUnread = ref(false);

function getIcon(type) {
    const icons = {
        campaign_success: 'pi pi-check-circle',
        campaign_failed: 'pi pi-times-circle',
        backing_refunded: 'pi pi-undo',
        backing_completed: 'pi pi-heart',
        deadline_approaching: 'pi pi-clock',
        campaign_update: 'pi pi-megaphone',
        system: 'pi pi-info-circle',
    };
    return icons[type] || 'pi pi-bell';
}

function getIconClass(type) {
    const classes = {
        campaign_success: 'bg-green-500/10',
        campaign_failed: 'bg-red-500/10',
        backing_refunded: 'bg-orange-500/10',
        backing_completed: 'bg-brand-500/10',
        deadline_approaching: 'bg-yellow-500/10',
        campaign_update: 'bg-purple-500/10',
        system: 'bg-blue-500/10',
    };
    return classes[type] || 'bg-navy-700/50';
}

function formatDate(dateStr) {
    const date = new Date(dateStr);
    const now = new Date();
    const diff = now - date;
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);

    if (minutes < 1) return 'Baru saja';
    if (minutes < 60) return `${minutes} menit lalu`;
    if (hours < 24) return `${hours} jam lalu`;
    if (days < 7) return `${days} hari lalu`;

    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

async function fetchNotifications() {
    loading.value = true;
    try {
        const res = await notificationApi.getAll();
        notifications.value = res.data.data.notifications.data;
        hasUnread.value = res.data.data.unread_count > 0;
    } catch (err) {
        console.error('Failed to fetch notifications:', err);
    } finally {
        loading.value = false;
    }
}

async function markAsRead(notification) {
    if (notification.read_at) return;
    try {
        await notificationApi.markAsRead(notification.id);
        notification.read_at = new Date().toISOString();
        hasUnread.value = notifications.value.some(n => !n.read_at);
    } catch (err) {
        console.error('Failed to mark as read:', err);
    }
}

async function markAllAsRead() {
    try {
        await notificationApi.markAllAsRead();
        notifications.value.forEach(n => {
            if (!n.read_at) n.read_at = new Date().toISOString();
        });
        hasUnread.value = false;
    } catch (err) {
        console.error('Failed to mark all as read:', err);
    }
}

onMounted(fetchNotifications);
</script>
