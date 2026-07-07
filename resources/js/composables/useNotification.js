import { ref } from 'vue';
import { notificationApi } from '@/services/api';
import dayjs from '@/plugins/dayjs';

/**
 * Composable for notification operations.
 * Wraps notificationApi to provide reactive state.
 */
export function useNotification() {
    const notifications = ref([]);
    const unreadCount = ref(0);
    const isLoading = ref(false);

    async function fetchNotifications(params = {}) {
        isLoading.value = true;
        try {
            const res = await notificationApi.getAll(params);
            notifications.value = res.data.data.notifications.data;
            unreadCount.value = res.data.data.unread_count;
        } catch (err) {
            console.error('Failed to fetch notifications:', err);
        } finally {
            isLoading.value = false;
        }
    }

    async function fetchUnreadCount() {
        try {
            const res = await notificationApi.getUnreadCount();
            unreadCount.value = res.data.data.unread_count;
        } catch {
            unreadCount.value = 0;
        }
    }

    async function markAsRead(notification) {
        if (notification.read_at) return;
        try {
            await notificationApi.markAsRead(notification.id);
            notification.read_at = dayjs().toISOString();
            unreadCount.value = Math.max(0, unreadCount.value - 1);
        } catch (err) {
            console.error('Failed to mark as read:', err);
        }
    }

    async function markAllAsRead() {
        try {
            await notificationApi.markAllAsRead();
            notifications.value.forEach(n => {
                if (!n.read_at) n.read_at = dayjs().toISOString();
            });
            unreadCount.value = 0;
        } catch (err) {
            console.error('Failed to mark all as read:', err);
        }
    }

    return {
        notifications,
        unreadCount,
        isLoading,
        fetchNotifications,
        fetchUnreadCount,
        markAsRead,
        markAllAsRead,
    };
}
