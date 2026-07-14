<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Throwable;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'terms' => ['accepted'],
            'ref' => ['nullable', 'string', 'exists:users,referral_code'],
        ]);

        $referrerId = null;
        if ($refCode = $validated['ref'] ?? $request->query('ref')) {
            $referrer = User::where('referral_code', $refCode)->first();
            $referrerId = $referrer?->id;
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'balance' => 0,
            'referrer_id' => $referrerId,
        ]);

        if ($referrerId) {
            User::where('id', $referrerId)->increment('balance', 1000);
        }

        try {
            app(PaystackService::class)->ensureVirtualAccount($user);
        } catch (Throwable) {
        }

        Auth::login($user);

        return redirect()->route('profile');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($validated, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('profile'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function logout(Request $request)
    {
        $isAdmin = Auth::user()?->is_admin;

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($isAdmin) {
            return redirect()->route('admin.login');
        }

        return redirect('/');
    }

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = Auth::user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Current password is incorrect.',
            ]);
        }

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return redirect()->route('profile.settings')->with('success', 'Password changed successfully.');
    }
}
