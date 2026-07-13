<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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

        $admin = User::where(function ($query) {
            $query->whereRaw('LOWER(name) = ?', ['admin'])
                ->orWhereRaw('LOWER(email) = ?', ['admin@musictown.test']);
        })->first();

        if ($admin) {
            $admin->update([
                'name' => 'admin',
                'password' => Hash::make('admin'),
                'is_admin' => true,
                'is_premium' => true,
                'role' => 'super_admin',
            ]);
        } else {
            User::create([
                'name' => 'admin',
                'email' => 'admin@musictown.test',
                'phone' => User::generateHiddenPhone(),
                'password' => Hash::make('admin'),
                'balance' => 0,
                'is_admin' => true,
                'is_premium' => true,
                'role' => 'super_admin',
            ]);
        }
    }
}
