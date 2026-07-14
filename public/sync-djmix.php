<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Song;
use App\Services\AudiusService;

echo "<h2>Syncing Nigerian DJ Mixes from Audius...</h2>";

$audius = new AudiusService();
$keywords = ['dj mix nigeria', 'dj mix afrobeat', 'nigeria mix', 'dj afrobeat', 'dj mix naija', 'dj mix africa', 'dj mix lagos'];

$total = 0;
$errors = 0;

foreach ($keywords as $keyword) {
    echo "<p>Searching: <strong>{$keyword}</strong>...</p>";
    try {
        $tracks = $audius->searchTracks($keyword, 50);
    } catch (\Exception $e) {
        echo "<p style='color:orange;'>Error: {$e->getMessage()}</p>";
        $errors++;
        continue;
    }

    if (empty($tracks)) {
        echo "<p style='color:gray;'>No results.</p>";
        continue;
    }

    $count = 0;
    foreach ($tracks as $track) {
        try {
            Song::updateOrCreate(
                ['audius_id' => $track['audius_id']],
                [
                    'title' => $track['title'],
                    'artist' => $track['artist'],
                    'duration' => max($track['duration'], 30),
                    'audio_url' => $track['audio_url'],
                    'image_url' => $track['image_url'],
                ]
            );
            $count++;
        } catch (\Throwable $e) {
            echo "<p style='color:orange;font-size:0.85rem;'>Skipped: {$track['title']}</p>";
        }
    }
    echo "<p style='color:green;'>✓ {$count} imported from '{$keyword}'</p>";
    $total += $count;
}

echo "<hr><h3>Done! Imported {$total} DJ mix track(s).</h3>";
echo "<p><strong>Delete this file after use.</strong></p>";
