<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create dedicated test accounts (known emails for login)
        User::factory()->admin()->create();
        User::factory()->create([
            'name' => 'Budi Santoso',
            'email' => 'creator@cofund.com',
            'role' => 'creator',
            'balance' => 5000000,
        ]);
        User::factory()->create([
            'name' => 'Siti Rahayu',
            'email' => 'creator2@cofund.com',
            'role' => 'creator',
            'balance' => 7500000,
        ]);
        User::factory()->create([
            'name' => 'Andi Pratama',
            'email' => 'backer@cofund.com',
            'role' => 'backer',
            'balance' => 3000000,
        ]);

        // Additional random users
        User::factory()->count(3)->creator()->create();
        User::factory()->count(5)->backer()->create();

        // Seeder categories & campaigns
        $this->call([
            CategorySeeder::class,
            CampaignSeeder::class,
        ]);
    }
}
