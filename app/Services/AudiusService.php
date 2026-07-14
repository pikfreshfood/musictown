<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AudiusService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = 'https://api.audius.co/v1';
    }

    public function searchTracks(string $query, int $limit = 50, int $offset = 0): array
    {
        $response = Http::withoutVerifying()
            ->acceptJson()
            ->connectTimeout(10)
            ->timeout(30)
            ->get($this->baseUrl . '/tracks/search', [
                'query' => $query,
                'limit' => min($limit, 100),
                'offset' => $offset,
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Audius API error: ' . $response->body());
        }

        $data = $response->json();
        return $this->normalizeTracks($data['data'] ?? []);
    }

    public function getTrending(string $genre = null, int $limit = 50): array
    {
        $params = ['limit' => min($limit, 100)];
        if ($genre) {
            $params['genre'] = $genre;
        }

        $response = Http::withoutVerifying()
            ->acceptJson()
            ->connectTimeout(10)
            ->timeout(30)
            ->get($this->baseUrl . '/tracks/trending', $params);

        if (!$response->successful()) {
            throw new \RuntimeException('Audius API error: ' . $response->body());
        }

        $data = $response->json();
        return $this->normalizeTracks($data['data'] ?? []);
    }

    protected function normalizeTracks(array $tracks): array
    {
        $normalized = [];
        foreach ($tracks as $track) {
            if (empty($track['stream']['url']) && empty($track['preview']['url'])) {
                continue;
            }

            $artworkUrl = '';
            if (!empty($track['artwork']['150x150'])) {
                $artworkUrl = $track['artwork']['150x150'];
            }

            $audioUrl = $track['stream']['url'] ?? $track['preview']['url'] ?? '';

            $normalized[] = [
                'audius_id' => $track['id'],
                'title' => $track['title'],
                'artist' => $track['user']['name'] ?? 'Unknown',
                'duration' => (int) ($track['duration'] ?? 0),
                'audio_url' => $audioUrl,
                'image_url' => $artworkUrl,
            ];
        }
        return $normalized;
    }
}
