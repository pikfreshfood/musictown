<?php

namespace App\Http\Controllers;

use App\Models\Listen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        Listen::create([
            'user_id' => $user->id,
            'song_id' => $songId,
            'listened_at' => now(),
        ]);

        return response()->json(['success' => true]);
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
