<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\CampaignTier;
use App\Models\CampaignImage;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CampaignSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $campaigns = [
            [
                'user_id' => 2, // creator user
                'category_id' => 1, // Pendidikan
                'title' => 'Bantu Pembangunan Perpustakaan Desa',
                'description' => 'Kami ingin membangun perpustakaan desa untuk anak-anak di Desa Sukamaju. Dengan adanya perpustakaan ini, anak-anak akan memiliki akses ke buku-buku berkualitas dan ruang belajar yang nyaman. Target kami adalah mengumpulkan dana untuk membangun gedung perpustakaan, membeli rak buku, dan menyediakan koleksi minimal 500 buku.',
                'target_amount' => 50000000,
                'collected_amount' => 32500000,
                'deadline' => Carbon::now()->addDays(45),
                'status' => 'active',
                'tiers' => [
                    ['name' => 'Donatur', 'min_amount' => 50000, 'quota' => 100, 'reward_description' => 'Ucapan terima kasih di media sosial'],
                    ['name' => 'Pendukung', 'min_amount' => 100000, 'quota' => 50, 'reward_description' => 'Nama tercantum di dinding apresiasi perpustakaan'],
                    ['name' => 'Pelopor', 'min_amount' => 500000, 'quota' => 20, 'reward_description' => 'Nama di papan apresiasi + buku personalized + sertifikat'],
                ],
            ],
            [
                'user_id' => 2,
                'category_id' => 3, // Teknologi
                'title' => 'Aplikasi Belajar Interaktif untuk Anak',
                'description' => 'Kembangkan aplikasi pembelajaran interaktif berbasis gamifikasi untuk anak-anak usia 6-12 tahun. Aplikasi ini akan mencakup mata pelajaran Matematika, Sains, dan Bahasa Indonesia dengan metode belajar yang menyenangkan melalui game dan animasi.',
                'target_amount' => 75000000,
                'collected_amount' => 45000000,
                'deadline' => Carbon::now()->addDays(30),
                'status' => 'active',
                'tiers' => [
                    ['name' => 'Early Adopter', 'min_amount' => 100000, 'quota' => 200, 'reward_description' => 'Akses premium aplikasi 3 bulan + sticker pack digital'],
                    ['name' => 'Beta Tester', 'min_amount' => 250000, 'quota' => 100, 'reward_description' => 'Akses lifetime + nama di credit aplikasi'],
                    ['name' => 'Investor Awal', 'min_amount' => 1000000, 'quota' => 20, 'reward_description' => 'Akses lifetime + laporan perkembangan + consultation call'],
                ],
            ],
            [
                'user_id' => 3,
                'category_id' => 4, // Sosial & Kemanusiaan
                'title' => 'Bantuan Untuk Korban Banjir Garut',
                'description' => 'Banjir bandang melanda Kabupaten Garut dan sekitarnya, ribuan warga kehilangan tempat tinggal. Kami menggalang dana untuk memberikan bantuan darurat berupa makanan, air bersih, pakaian, dan obat-obatan bagi para korban yang membutuhkan.',
                'target_amount' => 100000000,
                'collected_amount' => 78500000,
                'deadline' => Carbon::now()->addDays(14),
                'status' => 'active',
                'tiers' => [
                    ['name' => 'Peduli', 'min_amount' => 25000, 'quota' => 500, 'reward_description' => 'Laporan penyaluran bantuan'],
                    ['name' => 'Dermawan', 'min_amount' => 100000, 'quota' => 200, 'reward_description' => 'Laporan penyaluran + sertifikat digital'],
                    ['name' => 'Sahabat Kemanusiaan', 'min_amount' => 500000, 'quota' => 50, 'reward_description' => 'Laporan lengkap + sertifikat + merchandise'],
                ],
            ],
            [
                'user_id' => 3,
                'category_id' => 6, // Seni & Budaya
                'title' => 'Festival Musik Tradisional Nusantara',
                'description' => 'Menggelar festival musik tradisional yang menampilkan alat-alat musik daerah dari seluruh Indonesia. Acara ini bertujuan untuk melestarikan warisan budaya musik tradisional dan memperkenalkannya kepada generasi muda.',
                'target_amount' => 150000000,
                'collected_amount' => 55000000,
                'deadline' => Carbon::now()->addDays(60),
                'status' => 'active',
                'tiers' => [
                    ['name' => 'Penonton', 'min_amount' => 50000, 'quota' => 300, 'reward_description' => 'Tiket masuk festival + stiker'],
                    ['name' => 'Pencinta Seni', 'min_amount' => 200000, 'quota' => 100, 'reward_description' => 'Tiket VIP + kaos eksklusif festival'],
                    ['name' => 'Pelindung Budaya', 'min_amount' => 1000000, 'quota' => 30, 'reward_description' => 'Tiket VIP + merchandise + nama di sponsor + sertifikat'],
                ],
            ],
            [
                'user_id' => 2,
                'category_id' => 5, // Lingkungan
                'title' => 'Gerakan Tanam 10.000 Pohon Mangrove',
                'description' => 'Mari bersama-sama menanam 10.000 pohon mangrove di pesisir Pantai Utara Jawa untuk mencegah abrasi dan menjaga ekosistem laut. Kegiatan ini akan melibatkan masyarakat lokal dan para relawan dari berbagai daerah.',
                'target_amount' => 25000000,
                'collected_amount' => 25000000,
                'deadline' => Carbon::now()->addDays(5),
                'status' => 'active',
                'tiers' => [
                    ['name' => 'Penanam', 'min_amount' => 25000, 'quota' => 500, 'reward_description' => '1 pohon mangrove atas namamu + e-sertifikat'],
                    ['name' => 'Pelestari', 'min_amount' => 100000, 'quota' => 100, 'reward_description' => '5 pohon mangrove + sertifikat + laporan dampak'],
                    ['name' => 'Pahlawan Bumi', 'min_amount' => 500000, 'quota' => 20, 'reward_description' => '20 pohon + sertifikat + merchandise + undangan acara tanam'],
                ],
            ],
            [
                'user_id' => 3,
                'category_id' => 8, // Kewirausahaan
                'title' => 'Produksi Keripik Pisang UMKM Lokal',
                'description' => 'Bantu UMKM lokal mengembangkan produksi keripik pisang dengan mesin penggorengan vakum modern. Dengan mesin baru ini, kapasitas produksi bisa meningkat 3 kali lipat dan membuka lapangan kerja baru bagi warga sekitar.',
                'target_amount' => 35000000,
                'collected_amount' => 12000000,
                'deadline' => Carbon::now()->addDays(21),
                'status' => 'active',
                'tiers' => [
                    ['name' => 'Pencicip', 'min_amount' => 50000, 'quota' => 100, 'reward_description' => 'Paket produk keripik pisang 3 varian'],
                    ['name' => 'Konsumen', 'min_amount' => 150000, 'quota' => 50, 'reward_description' => 'Paket produk + diskon 20% seumur hidup'],
                    ['name' => 'Investor', 'min_amount' => 1000000, 'quota' => 10, 'reward_description' => 'Paket produk premium + profit sharing 3 bulan + nama di kemasan'],
                ],
            ],
        ];

        foreach ($campaigns as $data) {
            $tiers = $data['tiers'];
            unset($data['tiers']);

            $campaign = Campaign::create($data);

            // Create tiers
            foreach ($tiers as $tierData) {
                CampaignTier::create([
                    'campaign_id' => $campaign->id,
                    'name' => $tierData['name'],
                    'min_amount' => $tierData['min_amount'],
                    'quota' => $tierData['quota'],
                    'remaining_quota' => $tierData['quota'],
                    'reward_description' => $tierData['reward_description'],
                ]);
            }
        }
    }
}
