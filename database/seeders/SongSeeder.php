<?php

namespace Database\Seeders;

use App\Models\Song;
use Illuminate\Database\Seeder;

class SongSeeder extends Seeder
{
    public function run(): void
    {
        Song::truncate();

        $songs = [
            ['title' => 'Jesus Iye', 'artist' => 'Nathaniel Bassey', 'duration' => 240],
            ['title' => 'Village Girl', 'artist' => 'Speed Darlington', 'duration' => 210],
            ['title' => 'Calculate', 'artist' => 'Kidd Carder', 'duration' => 200],
            ['title' => 'Odo', 'artist' => 'Mr P Ft Stonebwoy', 'duration' => 220],
            ['title' => 'Sweet Love', 'artist' => 'Burna Boy', 'duration' => 240],
            ['title' => 'Laho (Remix)', 'artist' => 'Shallipopi Ft. Burna Boy', 'duration' => 210],
        ];

        foreach ($songs as $song) {
            Song::create($song);
        }
    }
}
