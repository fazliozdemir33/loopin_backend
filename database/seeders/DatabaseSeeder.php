<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        
        // Seeding with some dummy users so we can test human-to-human
        User::factory(10)->create()->each(function ($user) {
            $user->update([
                'latitude' => 41.0082 + (mt_rand(-1000, 1000) / 10000), // Around Istanbul
                'longitude' => 28.9784 + (mt_rand(-1000, 1000) / 10000),
                'avatar_url' => 'https://i.pravatar.cc/150?u='.$user->id,
            ]);
        });
    }
}
