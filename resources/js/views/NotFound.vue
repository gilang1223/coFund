<template>
    <div class="min-h-screen flex items-center justify-center px-4 bg-navy-950">
        <div class="text-center animate-fade-in">
            <div class="text-8xl font-bold text-brand-500/20 mb-4">404</div>
            <h1 class="text-2xl font-bold text-white mb-2">Halaman Tidak Ditemukan</h1>
            <p class="text-gray-500 mb-8">Halaman yang Anda cari tidak tersedia atau telah dipindahkan.</p>
            <router-link
                to="/"
                class="inline-flex items-center px-6 py-3 bg-brand-500 text-white font-medium rounded-md hover:bg-brand-600 transition-all duration-200"
            >
                <i class="pi pi-home mr-2"></i>
                Kembali ke Beranda
            </router-link>
        </div>
    </div>
</template>
