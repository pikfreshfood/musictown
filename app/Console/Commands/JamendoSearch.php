<?php

namespace App\Console\Commands;

use App\Models\Song;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class JamendoSearch extends Command
{
    protected $signature = 'jamendo:search
        {query : Search term (searches track names and artist names)}
        {--limit=200 : Number of tracks to fetch}';

    protected $description = 'Search and import tracks from Jamendo by keyword';

    public function handle(): int
    {
        $query = $this->argument('query');
        $limit = min((int) $this->option('limit'), 200);
        $clientId = config('jamendo.client_id');

        if (!$clientId || $clientId === 'your_jamendo_client_id_here') {
            $this->error('Jamendo Client ID is not configured.');
            return Command::FAILURE;
        }

        $this->info("Searching Jamendo for: {$query}");

        $allTracks = [];
        $offset = 0;
        $perPage = min(200, $limit);

        while ($offset < $limit) {
            $response = Http::withoutVerifying()
                ->acceptJson()
                ->connectTimeout(10)
                ->timeout(30)
                ->get('https://api.jamendo.com/v3.0/tracks/', [
                    'client_id' => $clientId,
                    'format' => 'json',
                    'limit' => $perPage,
                    'offset' => $offset,
                    'hashtml' => 'true',
                    'include' => 'musicinfo',
                    'search' => $query,
                    'order' => 'popularity_week',
                ]);

            if (!$response->successful()) {
                $this->error('API error: ' . $response->body());
                break;
            }

            $data = $response->json();
            $tracks = $data['results'] ?? [];

            if (empty($tracks)) {
                break;
            }

            $allTracks = array_merge($allTracks, $tracks);
            $offset += count($tracks);

            if (count($tracks) < $perPage) {
                break;
            }
        }

        if (empty($allTracks)) {
            $this->warn("No tracks found for '{$query}'.");
            return Command::SUCCESS;
        }

        $this->info("Found " . count($allTracks) . " tracks. Importing...");

        $imported = 0;
        $skipped = 0;

        foreach ($allTracks as $track) {
            try {
                Song::updateOrCreate(
                    ['jamendo_id' => $track['id']],
                    [
                        'title' => $track['name'],
                        'artist' => $track['artist_name'],
                        'duration' => max((int) ($track['duration'] ?? 0), 30),
                        'audio_url' => $track['audio'],
                        'image_url' => $track['album_image'] ?? null,
                    ]
                );
                $imported++;
            } catch (\Throwable) {
                $skipped++;
            }
        }

        $this->info("Done! Imported {$imported} track(s), skipped {$skipped}.");
        return Command::SUCCESS;
    }
}
