<?php

namespace App\Console\Commands;

use App\Models\Song;
use App\Services\JamendoService;
use Illuminate\Console\Command;

class SyncJamendo extends Command
{
    protected $signature = 'jamendo:sync
        {--limit=50 : Number of tracks to fetch per page (max 200)}
        {--total=200 : Total number of tracks to fetch (will paginate)}
        {--tag= : Filter by tag/genre (e.g. rock, pop, electronic)}';

    protected $description = 'Import tracks from Jamendo API into the songs table';

    public function handle(JamendoService $jamendo): int
    {
        $perPage = min((int) $this->option('limit'), 200);
        $total = (int) $this->option('total');
        $tag = $this->option('tag');

        $params = ['limit' => $perPage];
        if ($tag && $tag !== 'all') {
            $params['tags'] = $tag;
        }

        $this->info('Fetching tracks from Jamendo...');

        $imported = 0;
        $skipped = 0;
        $offset = 0;

        $progress = $this->output->createProgressBar($total);
        $progress->start();

        while ($offset < $total) {
            $params['offset'] = $offset;
            $params['limit'] = min($perPage, $total - $offset);

            try {
                $tracks = $jamendo->getTracks($params);
            } catch (\RuntimeException $e) {
                $this->error($e->getMessage());
                return Command::FAILURE;
            }

            if (empty($tracks)) {
                break;
            }

            foreach ($tracks as $track) {
                try {
                    Song::updateOrCreate(
                        ['jamendo_id' => $track['jamendo_id']],
                        [
                            'title' => $track['title'],
                            'artist' => $track['artist'],
                            'duration' => max($track['duration'], 30),
                            'audio_url' => $track['audio_url'],
                            'image_url' => $track['image_url'],
                        ]
                    );
                    $imported++;
                } catch (\Throwable) {
                    $skipped++;
                }
                $progress->advance();
            }

            $offset += count($tracks);

            if (count($tracks) < $params['limit']) {
                break;
            }
        }

        $progress->finish();
        $this->newLine();
        $this->info("Done! Imported {$imported} new track(s), skipped {$skipped} existing.");

        return Command::SUCCESS;
    }
}
