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
import { onMounted, onUnmounted, computed } from 'vue';
import { useNotification } from '@/composables/useNotification';
import dayjs from '@/plugins/dayjs';

const {
    notifications,
    unreadCount,
    isLoading: loading,
    fetchNotifications,
    markAsRead,
    markAllAsRead,
} = useNotification();

let pollInterval = null;

const hasUnread = computed(() => unreadCount.value > 0);

function getIcon(type) {
    const icons = {
        campaign_success: 'pi pi-check-circle',
        campaign_failed: 'pi pi-times-circle',
        campaign_status: 'pi pi-flag',
        backing_refunded: 'pi pi-undo',
        backing_completed: 'pi pi-heart',
        backing_received: 'pi pi-heart',
        deadline_approaching: 'pi pi-clock',
        campaign_update: 'pi pi-megaphone',
        creator_request: 'pi pi-star',
        account: 'pi pi-shield',
        system: 'pi pi-info-circle',
    };
    return icons[type] || 'pi pi-bell';
}

function getIconClass(type) {
    const classes = {
        campaign_success: 'bg-green-500/10 text-green-500 dark:text-green-400',
        campaign_failed: 'bg-red-500/10 text-red-500 dark:text-red-400',
        campaign_status: 'bg-brand-500/10 text-brand-500 dark:text-brand-400',
        backing_refunded: 'bg-orange-500/10 text-orange-500 dark:text-orange-400',
        backing_completed: 'bg-brand-500/10 text-brand-500 dark:text-brand-400',
        backing_received: 'bg-brand-500/10 text-brand-500 dark:text-brand-400',
        deadline_approaching: 'bg-yellow-500/10 text-yellow-600 dark:text-yellow-400',
        campaign_update: 'bg-purple-500/10 text-purple-500 dark:text-purple-400',
        creator_request: 'bg-yellow-500/10 text-yellow-600 dark:text-yellow-400',
        account: 'bg-orange-500/10 text-orange-500 dark:text-orange-400',
        system: 'bg-blue-500/10 text-blue-500 dark:text-blue-400',
    };
    return classes[type] || 'bg-navy-700/50 text-gray-500 dark:text-gray-400';
}

function formatDate(dateStr) {
    const date = dayjs(dateStr);
    const diffDays = dayjs().diff(date, 'day');

    if (diffDays < 7) {
        return date.fromNow();
    }

    return date.format('D MMM YYYY, HH:mm');
}

onMounted(() => {
    fetchNotifications();
    // Poll every 5 seconds silently in the background
    pollInterval = setInterval(() => {
        fetchNotifications({}, true);
    }, 5000);
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
});
</script>
