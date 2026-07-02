<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Pendidikan', 'slug' => 'pendidikan'],
            ['name' => 'Kesehatan', 'slug' => 'kesehatan'],
            ['name' => 'Teknologi', 'slug' => 'teknologi'],
            ['name' => 'Sosial & Kemanusiaan', 'slug' => 'sosial-kemanusiaan'],
            ['name' => 'Lingkungan', 'slug' => 'lingkungan'],
            ['name' => 'Seni & Budaya', 'slug' => 'seni-budaya'],
            ['name' => 'Bencana Alam', 'slug' => 'bencana-alam'],
            ['name' => 'Kewirausahaan', 'slug' => 'kewirausahaan'],
            ['name' => 'Olahraga', 'slug' => 'olahraga'],
            ['name' => 'Komunitas', 'slug' => 'komunitas'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
