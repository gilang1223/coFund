<template>
    <Dialog
        v-model:visible="visible"
        :modal="true"
        :closable="true"
        :dismissable-mask="true"
        class="w-full max-w-md"
        :header="null"
    >
        <div class="text-center py-2">
            <!-- Icon -->
            <div class="w-16 h-16 rounded-full bg-brand-500/10 flex items-center justify-center mx-auto mb-4">
                <i class="pi pi-star text-brand-400 text-2xl"></i>
            </div>

            <!-- Title -->
            <h3 class="text-xl font-bold text-white mb-2">
                Hanya Creator yang Bisa Membuat Kampanye
            </h3>

            <!-- Description -->
            <p class="text-gray-400 text-sm leading-relaxed mb-6">
                Untuk membuat kampanye, Anda harus terdaftar sebagai <strong class="text-brand-400">Creator</strong>.
                Backer belum bisa membuat kampanye, tapi Anda bisa mengajukan permohonan menjadi Creator melalui halaman profil.
            </p>

            <!-- Steps -->
            <div class="bg-navy-800/50 border border-navy-700 rounded-lg p-4 text-left space-y-3 mb-6">
                <div class="flex items-start gap-3">
                    <div class="w-6 h-6 rounded-full bg-brand-500/20 flex items-center justify-center shrink-0 mt-0.5">
                        <span class="text-brand-400 text-xs font-bold">1</span>
                    </div>
                    <p class="text-sm text-gray-300">
                        Buka halaman <strong class="text-gray-200">Profil</strong> Anda
                    </p>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-6 h-6 rounded-full bg-brand-500/20 flex items-center justify-center shrink-0 mt-0.5">
                        <span class="text-brand-400 text-xs font-bold">2</span>
                    </div>
                    <p class="text-sm text-gray-300">
                        Isi alasan mengapa ingin menjadi Creator
                    </p>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-6 h-6 rounded-full bg-brand-500/20 flex items-center justify-center shrink-0 mt-0.5">
                        <span class="text-brand-400 text-xs font-bold">3</span>
                    </div>
                    <p class="text-sm text-gray-300">
                        Ajukan permohonan, tunggu review admin
                    </p>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-6 h-6 rounded-full bg-green-500/20 flex items-center justify-center shrink-0 mt-0.5">
                        <i class="pi pi-check text-green-400 text-xs"></i>
                    </div>
                    <p class="text-sm text-gray-300">
                        Setelah disetujui, Anda bisa membuat kampanye!
                    </p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col gap-2">
                <Button
                    label="Ajukan Menjadi Creator"
                    icon="pi pi-star"
                    class="w-full"
                    @click="$emit('goToProfile')"
                />
                <Button
                    label="Nanti Saja"
                    severity="secondary"
                    class="w-full"
                    @click="visible = false"
                    text
                />
            </div>
        </div>
    </Dialog>
</template>

<script setup>
import { computed } from 'vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';

const props = defineProps({
    modelValue: {
        type: Boolean,
        required: true,
    },
});

const emit = defineEmits(['update:modelValue', 'goToProfile']);

const visible = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val),
});
</script>
