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

        $alreadyListened = Listen::where('user_id', $user->id)
            ->where('song_id', $songId)
            ->where('listened_date', today())
            ->exists();

        if ($alreadyListened) {
            return response()->json([
                'error' => 'You have already listened to this song today.',
            ], 403);
        }

        Listen::create([
            'user_id' => $user->id,
            'song_id' => $songId,
            'listened_date' => today(),
        ]);

        return response()->json(['success' => true]);
    }

    public function tick(Request $request, $songId)
    {
        $user = Auth::user();

        $user->increment('balance', 100);

        return response()->json([
            'success' => true,
            'balance' => $user->fresh()->balance,
        ]);
    }
}
