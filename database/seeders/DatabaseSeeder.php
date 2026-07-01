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
        $this->call([
            SongSeeder::class,
        ]);

        $admin = \App\Models\User::where('email', 'admin@pulsewave.test')->first();
        if (!$admin) {
            \App\Models\User::create([
                'name' => 'admin',
                'email' => 'admin@pulsewave.test',
                'phone' => '0000000000',
                'password' => bcrypt('admin'),
                'balance' => 0,
                'is_admin' => true,
                'is_premium' => true,
                'role' => 'super_admin',
            ]);
        }
    }
}
