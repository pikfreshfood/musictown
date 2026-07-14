<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class JamendoService
{
    protected string $clientId;
    protected string $baseUrl;

    public function __construct()
    {
        $this->clientId = config('jamendo.client_id');
        $this->baseUrl = config('jamendo.base_url');
    }

    public function getTracks(array $params = []): array
    {
        if (!$this->clientId || $this->clientId === 'your_jamendo_client_id_here') {
            throw new \RuntimeException('Jamendo Client ID is not configured. Set JAMENDO_CLIENT_ID in your .env file.');
        }

        $defaults = [
            'client_id' => $this->clientId,
            'format' => 'json',
            'limit' => 50,
            'hashtml' => 'true',
            'include' => 'musicinfo',
            'order' => 'popularity_week',
        ];

        $response = Http::withoutVerifying()
            ->acceptJson()
            ->connectTimeout(10)
            ->timeout(30)
            ->get($this->baseUrl . '/tracks/', array_merge($defaults, $params));

        if (!$response->successful()) {
            throw new \RuntimeException('Jamendo API error: ' . $response->body());
        }

        $data = $response->json();

        if (!($data['headers']['status'] ?? false) || !isset($data['results'])) {
            throw new \RuntimeException('Jamendo API returned an unexpected response.');
        }

        return $this->normalizeTracks($data['results']);
    }

    protected function normalizeTracks(array $tracks): array
    {
        return array_map(fn ($track) => [
            'jamendo_id' => $track['id'],
            'title' => $track['name'],
            'artist' => $track['artist_name'],
            'duration' => (int) ($track['duration'] ?? 0),
            'audio_url' => $track['audio'],
            'image_url' => $track['album_image'] ?? null,
        ], $tracks);
    }

    public static function getGenres(): array
    {
        return [
            'all' => 'All',
            'rock' => 'Rock',
            'pop' => 'Pop',
            'electronic' => 'Electronic',
            'hiphop' => 'Hip-Hop',
            'jazz' => 'Jazz',
            'classical' => 'Classical',
            'ambient' => 'Ambient',
            'reggae' => 'Reggae',
            'country' => 'Country',
            'blues' => 'Blues',
            'folk' => 'Folk',
            'metal' => 'Metal',
            'soul' => 'Soul',
            'punk' => 'Punk',
            'latin' => 'Latin',
            'world' => 'World',
            'newage' => 'New Age',
            'afrobeat' => 'Afrobeat',
            'afropop' => 'Afropop',
            'african' => 'African',
            'nigerian' => 'Nigerian',
            'dancehall' => 'Dancehall',
            'rnb' => 'R&B',
            'gospel' => 'Gospel',
            'karaoke' => 'Karaoke',
            'instrumental' => 'Instrumental',
        ];
    }
}
