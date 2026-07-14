<?php

namespace App\Console\Commands;

use App\Models\Song;
use App\Services\AudiusService;
use Illuminate\Console\Command;

class SyncAudius extends Command
{
    protected $signature = 'audius:sync
        {query? : Search term for Nigerian/African music}
        {--limit=200 : Number of tracks to fetch}
        {--trending : Fetch trending tracks instead of search}';

    protected $description = 'Import tracks from Audius API';

    public function handle(AudiusService $audius): int
    {
        $query = $this->argument('query');
        $limit = (int) $this->option('limit');
        $trending = $this->option('trending');

        $keywords = $query ? [$query] : ['nigeria', 'naija', 'afrobeat', 'afropop', 'lagos', 'africa', 'burna', 'wizkid', 'davido'];

        $totalImported = 0;
        $totalSkipped = 0;

        foreach ($keywords as $keyword) {
            if ($totalImported >= $limit) break;

            $this->info("Searching Audius for: {$keyword}");
            $perPage = min(50, $limit - $totalImported);

            try {
                $tracks = $audius->searchTracks($keyword, $perPage);
            } catch (\RuntimeException $e) {
                $this->warn("  Search failed: {$e->getMessage()}");
                continue;
            }

            if (empty($tracks)) {
                $this->warn("  No results for '{$keyword}'");
                continue;
            }

            foreach ($tracks as $track) {
                if ($totalImported >= $limit) break 2;

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
                    $totalImported++;
                } catch (\Throwable) {
                    $totalSkipped++;
                }
            }

            $this->info("  + " . count($tracks) . " from '{$keyword}'");
        }

        $this->newLine();
        $this->info("Done! Imported {$totalImported} track(s), skipped {$totalSkipped}.");

        return Command::SUCCESS;
    }
}
