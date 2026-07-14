<?php

namespace App\Http\Controllers;

use App\Models\Listen;
use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class MusicController extends Controller
{
    public function play(Request $request, $songId)
    {
        $user = Auth::user();

        $lastListen = Listen::where('user_id', $user->id)
            ->where('song_id', $songId)
            ->latest('listened_at')
            ->first();

        if ($lastListen && $lastListen->listened_at->gt(now()->subMinutes(10))) {
            $minsLeft = 10 - now()->diffInMinutes($lastListen->listened_at);
            return response()->json([
                'error' => 'Please check back in ' . $minsLeft . ' min.',
                'cooldown' => $minsLeft,
            ], 403);
        }

        $song = Song::findOrFail($songId);

        Listen::create([
            'user_id' => $user->id,
            'song_id' => $songId,
            'listened_at' => now(),
        ]);

        $audioUrl = $song->audio_url ? (str_starts_with($song->audio_url, 'http') ? $song->audio_url : asset('storage/' . $song->audio_url)) : '';

        return response()->json([
            'success' => true,
            'audio_url' => $audioUrl,
        ]);
    }

    public function loadMore(Request $request)
    {
        $q = $request->input('q');
        $page = (int) $request->input('page', 2);

        $songs = Song::when($q, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('artist', 'like', "%{$search}%");
            });
        })->paginate(10, ['*'], 'page', $page);

        $html = '';
        foreach ($songs as $song) {
            $mins = intdiv($song->duration, 60);
            $secs = $song->duration % 60;
            $audioUrl = $song->audio_url ? (str_starts_with($song->audio_url, 'http') ? $song->audio_url : asset('storage/' . $song->audio_url)) : '';

            $html .= '<div class="song-card" data-song-id="' . $song->id . '" data-duration="' . $song->duration . '" data-audio-url="' . e($audioUrl) . '" style="cursor:default;">';
            $html .= '<button class="play-btn" data-song-id="' . $song->id . '" data-title="' . e($song->title) . '" data-artist="' . e($song->artist) . '" aria-label="Play ' . e($song->title) . '">';
            $html .= '<span style="position:absolute;left:15px;top:11px;border-bottom:7px solid transparent;border-left:10px solid white;border-top:7px solid transparent;"></span>';
            $html .= '</button>';
            $html .= '<span style="flex:1;min-width:0;">';
            $html .= '<strong>' . e($song->title) . '</strong>';
            if ($song->image_url) {
                $html .= '<img src="' . e($song->image_url) . '" alt="" style="width:20px;height:20px;border-radius:3px;vertical-align:middle;margin-left:4px;">';
            }
            $html .= '<small class="song-artist">' . e($song->artist) . '</small>';
            $html .= '</span>';
            $html .= '<small class="song-duration">' . $mins . ':' . str_pad($secs, 2, '0') . '</small>';
            if ($song->audio_url) {
                $html .= '<a href="' . route('music.download', $song->id) . '" class="download-btn" title="Download" style="color:var(--blue-soft);text-decoration:none;padding:2px 6px;font-size:0.8rem;">&#8595;</a>';
            }
            $html .= '</div>';
        }

        return response()->json([
            'html' => $html,
            'hasMore' => $songs->hasMorePages(),
            'nextPage' => $page + 1,
        ]);
    }

    public function download(Request $request, $songId)
    {
        $song = Song::findOrFail($songId);
        $url = $song->audio_url;

        if (!$url) {
            abort(404);
        }

        if (!str_starts_with($url, 'http')) {
            $url = asset('storage/' . $url);
            return redirect($url);
        }

        $filename = preg_replace('/[^a-zA-Z0-9\-\_\. ]/', '', $song->title) . '.mp3';
        if (empty(trim($filename))) {
            $filename = 'song_' . $song->id . '.mp3';
        }

        try {
            $response = Http::withoutVerifying()
                ->timeout(60)
                ->get($url);

            if (!$response->successful()) {
                return redirect($url);
            }

            return response($response->body(), 200, [
                'Content-Type' => 'audio/mpeg',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Content-Length' => strlen($response->body()),
            ]);
        } catch (\Throwable) {
            return redirect($url);
        }
    }

    public function search(Request $request)
    {
        $q = $request->input('q');
        if (!$q || strlen($q) < 1) {
            return response()->json([]);
        }

        $songs = Song::where('title', 'like', "%{$q}%")
            ->orWhere('artist', 'like', "%{$q}%")
            ->limit(5)
            ->get(['id', 'title', 'artist', 'duration', 'audio_url', 'image_url']);

        return response()->json($songs);
    }

    public function tick(Request $request, $songId)
    {
        $user = Auth::user();

        $user->increment('balance', 5);

        return response()->json([
            'success' => true,
            'balance' => $user->fresh()->balance,
        ]);
    }
}
