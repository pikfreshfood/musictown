<?php

namespace App\Http\Controllers;

use App\Models\Listen;
use App\Models\NcoinCode;
use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $songs = Song::paginate(10);

        $listenedToday = Listen::where('user_id', $user->id)
            ->where('listened_date', today())
            ->pluck('song_id')
            ->toArray();

        return view('profile.index', compact('user', 'songs', 'listenedToday'));
    }

    public function settings()
    {
        $user = Auth::user();
        return view('profile.settings', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'phone' => ['required', 'string', 'max:30'],
        ]);

        $user->update($validated);

        return redirect()->route('profile.settings')->with('success', 'Profile updated successfully.');
    }

    public function withdrawal()
    {
        $user = Auth::user();

        return redirect()->route('premium.index');
    }

    public function history()
    {
        $user = Auth::user();
        $withdrawals = $user->withdrawals()->latest()->get();
        return view('profile.history', compact('user', 'withdrawals'));
    }

    public function withdraw(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'bank_name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:20'],
            'account_name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:10000', 'max:' . $user->balance],
            'ncoin' => ['required', 'string'],
        ]);

        $ncoinCode = NcoinCode::where('code', $validated['ncoin'])->where('is_used', false)->first();

        if (!$ncoinCode) {
            return response()->json(['error' => 'Invalid or already used Ncoin code. Transaction declined.'], 422);
        }

        $ncoinCode->update([
            'is_used' => true,
            'used_by_user_id' => $user->id,
            'used_at' => now(),
        ]);

        $withdrawal = $user->withdrawals()->create([
            'bank_name' => $validated['bank_name'],
            'account_number' => $validated['account_number'],
            'account_name' => $validated['account_name'],
            'amount' => $validated['amount'],
            'status' => 'pending',
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Withdrawal request submitted successfully.']);
        }

        return redirect()->route('profile.withdrawal')->with('success', 'Withdrawal request submitted successfully.');
    }

    public function verifyAccount(Request $request)
    {
        $validated = $request->validate([
            'account_number' => ['required', 'string', 'size:10'],
            'bank_code' => ['required', 'string'],
        ]);

        $response = Http::withoutVerifying()->withHeaders([
            'Authorization' => 'Bearer ' . config('services.paystack.secret_key'),
        ])->get('https://api.paystack.co/bank/resolve', [
            'account_number' => $validated['account_number'],
            'bank_code' => $validated['bank_code'],
        ]);

        $data = $response->json();

        if ($response->successful() && ($data['status'] ?? false)) {
            return response()->json([
                'status' => true,
                'account_name' => $data['data']['account_name'] ?? null,
                'account_number' => $data['data']['account_number'] ?? null,
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => $data['message'] ?? 'Account verification failed.',
        ], 422);
    }
}
