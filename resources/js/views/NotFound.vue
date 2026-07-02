<template>
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="text-center">
            <div class="text-8xl font-bold text-blue-600 mb-4">404</div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Halaman Tidak Ditemukan</h1>
            <p class="text-gray-600 mb-8">Halaman yang Anda cari tidak tersedia atau telah dipindahkan.</p>
            <router-link
                to="/"
                class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 transition-colors"
            >
                <i class="pi pi-home mr-2"></i>
                Kembali ke Beranda
            </router-link>
        </div>
    </div>
</template>
