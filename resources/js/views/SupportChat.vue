<template>
    <div class="container-page max-w-3xl mx-auto animate-fade-in">
        <div class="mb-6">
            <h1 class="text-2xl md:text-3xl font-bold text-white">Hubungi Admin</h1>
            <p class="text-gray-500 mt-1">Kirim pesan ke admin untuk bantuan atau pertanyaan</p>
        </div>

        <!-- Messages Container -->
        <div class="card p-4 mb-4" style="min-height: 400px;">
            <div v-if="isLoading" class="flex items-center justify-center py-20">
                <i class="pi pi-spin pi-spinner text-gray-500 text-2xl"></i>
            </div>

            <div v-else-if="messages.length === 0" class="text-center py-16">
                <div class="w-16 h-16 rounded-md bg-navy-800 flex items-center justify-center mx-auto mb-4">
                    <i class="pi pi-comments text-gray-600 text-2xl"></i>
                </div>
                <p class="text-gray-500">Belum ada pesan. Kirim pesan ke admin untuk memulai.</p>
            </div>

            <div v-else ref="messagesContainer" class="space-y-3 max-h-96 overflow-y-auto pr-1">
                <div
                    v-for="msg in messages"
                    :key="msg.id"
                    :class="[
                        'flex w-full',
                        msg.is_from_admin ? 'justify-start' : 'justify-end'
                    ]"
                >
                    <div
                        :class="[
                            'max-w-[80%] p-3 rounded-lg',
                            msg.is_from_admin
                                ? 'bg-navy-800 border border-navy-700 text-gray-300 rounded-bl-sm'
                                : 'bg-brand-500/20 border border-brand-500/20 text-brand-100 rounded-br-sm'
                        ]"
                    >
                        <p class="text-sm whitespace-pre-wrap">{{ msg.message }}</p>
                        <div :class="['flex items-center gap-2 mt-1.5', msg.is_from_admin ? '' : 'justify-end']">
                            <span class="text-[10px] text-gray-600">
                                {{ formatTime(msg.created_at) }}
                            </span>
                            <span v-if="msg.is_from_admin" class="text-[10px] text-brand-400 font-medium">Admin</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Input -->
        <div class="card p-3 flex gap-3">
            <textarea
                v-model="newMessage"
                rows="2"
                placeholder="Tulis pesan Anda..."
                class="flex-1 bg-navy-800 border border-navy-700 rounded-md px-3 py-2 text-sm text-gray-300 focus:outline-none focus:border-brand-500/50 resize-none"
                @keydown.enter.exact="sendMessage"
            ></textarea>
            <button
                @click="sendMessage"
                :disabled="!newMessage.trim() || isSending"
                class="shrink-0 self-end px-4 py-2 bg-brand-500 text-white text-sm font-medium rounded-md hover:bg-brand-600 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <i v-if="isSending" class="pi pi-spin pi-spinner mr-1"></i>
                <i v-else class="pi pi-send mr-1"></i>
                Kirim
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, nextTick, watch } from 'vue';
import { supportApi } from '@/services/api';
import { useToast } from 'vue-toastification';
import dayjs from '@/plugins/dayjs';

const toast = useToast();
const messages = ref([]);
const newMessage = ref('');
const isLoading = ref(true);
const isSending = ref(false);
const messagesContainer = ref(null);

function formatTime(date) {
    if (!date) return '';
    return dayjs(date).format('D MMM HH:mm');
}

async function scrollToBottom() {
    await nextTick();
    if (messagesContainer.value) {
        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
}

async function fetchMessages() {
    isLoading.value = true;
    try {
        const res = await supportApi.getMessages();
        messages.value = res.data.data;
        await scrollToBottom();
    } catch {
        messages.value = [];
    } finally {
        isLoading.value = false;
    }
}

async function sendMessage() {
    if (!newMessage.value.trim() || isSending.value) return;
    isSending.value = true;
    try {
        await supportApi.sendMessage(newMessage.value.trim());
        newMessage.value = '';
        await fetchMessages();
    } catch {
        toast.error('Gagal mengirim pesan. Silakan coba lagi.');
    } finally {
        isSending.value = false;
    }
}

watch(messages, async () => {
    await scrollToBottom();
}, { deep: true });

onMounted(() => {
    fetchMessages();
});
</script>
