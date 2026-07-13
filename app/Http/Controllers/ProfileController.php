<?php

namespace App\Http\Controllers;

use App\Models\Listen;
use App\Models\NcoinCode;
use App\Models\Song;
use App\Models\WalletFunding;
use App\Models\Withdrawal;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Throwable;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $songs = Song::paginate(10);

        $listenedRecent = Listen::where('user_id', $user->id)
            ->where('listened_at', '>=', now()->subMinutes(10))
            ->pluck('song_id')
            ->toArray();

        return view('profile.index', compact('user', 'songs', 'listenedRecent'));
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
        ]);

        $user->update($validated);

        return redirect()->route('profile.settings')->with('success', 'Profile updated successfully.');
    }

    public function withdrawal()
    {
        $user = Auth::user();

        return redirect()->route('premium.index');
    }

    public function banks()
    {
        try {
            $request = Http::withoutVerifying()
                ->acceptJson()
                ->connectTimeout(3)
                ->timeout(8);

            if (config('services.paystack.secret_key')) {
                $request = $request->withToken(config('services.paystack.secret_key'));
            }

            $response = $request->get('https://api.paystack.co/bank', [
                'country' => 'nigeria',
            ]);

            $data = $response->json();

            if ($response->successful() && ($data['status'] ?? false) && is_array($data['data'] ?? null)) {
                $banks = collect($data['data'])
                    ->map(fn ($bank) => [
                        'name' => $bank['name'] ?? null,
                        'code' => $bank['code'] ?? null,
                    ])
                    ->filter(fn ($bank) => filled($bank['name']) && filled($bank['code']))
                    ->unique('code')
                    ->sortBy('name')
                    ->values();

                if ($banks->isNotEmpty()) {
                    return response()->json($banks);
                }
            }
        } catch (Throwable) {
            // Use the built-in bank list when the external provider is slow or unavailable.
        }

        return response()->json($this->fallbackBanks());
    }

    public function history()
    {
        $user = Auth::user();
        $withdrawals = $user->withdrawals()->latest()->get();
        return view('profile.history', compact('user', 'withdrawals'));
    }

    public function receipt(Withdrawal $withdrawal)
    {
        $user = Auth::user();

        if ($withdrawal->user_id !== $user->id) {
            abort(403);
        }

        $transactionId = 'trx_784' . str_pad($withdrawal->id, 6, '0', STR_PAD_LEFT);

        return view('profile.withdrawal-receipt', compact('user', 'withdrawal', 'transactionId'));
    }

    private function tierMaxWithdrawal(string $tier): int
    {
        return match ($tier) {
            'tier1' => 50000,
            'tier2' => 500000,
            'tier3' => PHP_INT_MAX,
            default => 10000,
        };
    }

    private function tierMaxWithdrawalsCount(string $tier): int
    {
        return match ($tier) {
            'tier1' => 1,
            'tier2' => 2,
            'tier3' => 3,
            default => 0,
        };
    }

    private function fallbackBanks(): array
    {
        return [
            ['name' => 'Access Bank', 'code' => '044'],
            ['name' => 'Access Diamond Bank', 'code' => '063'],
            ['name' => 'Citibank Nigeria', 'code' => '023'],
            ['name' => 'Ecobank Nigeria', 'code' => '050'],
            ['name' => 'Fidelity Bank', 'code' => '070'],
            ['name' => 'First Bank of Nigeria', 'code' => '011'],
            ['name' => 'First City Monument Bank (FCMB)', 'code' => '214'],
            ['name' => 'Globus Bank', 'code' => '001'],
            ['name' => 'Guaranty Trust Bank (GTBank)', 'code' => '058'],
            ['name' => 'Heritage Bank', 'code' => '030'],
            ['name' => 'Jaiz Bank', 'code' => '301'],
            ['name' => 'Keystone Bank', 'code' => '082'],
            ['name' => 'Kuda Microfinance Bank', 'code' => '50211'],
            ['name' => 'Lotus Bank', 'code' => '303'],
            ['name' => 'Moniepoint Microfinance Bank', 'code' => '50515'],
            ['name' => 'OPay', 'code' => '100004'],
            ['name' => 'Paga', 'code' => '100002'],
            ['name' => 'Palmpay', 'code' => '100003'],
            ['name' => 'Parallex Bank', 'code' => '526'],
            ['name' => 'Polaris Bank', 'code' => '076'],
            ['name' => 'PremiumTrust Bank', 'code' => '105'],
            ['name' => 'Providus Bank', 'code' => '101'],
            ['name' => 'Sparkle Microfinance Bank', 'code' => '51310'],
            ['name' => 'Stanbic IBTC Bank', 'code' => '221'],
            ['name' => 'Standard Chartered Bank', 'code' => '068'],
            ['name' => 'Sterling Bank', 'code' => '232'],
            ['name' => 'Suntrust Bank', 'code' => '100'],
            ['name' => 'TAJ Bank', 'code' => '302'],
            ['name' => 'Titan Trust Bank', 'code' => '102'],
            ['name' => 'UBA (United Bank for Africa)', 'code' => '033'],
            ['name' => 'Union Bank of Nigeria', 'code' => '032'],
            ['name' => 'Unity Bank', 'code' => '215'],
            ['name' => 'VFD Microfinance Bank', 'code' => '50468'],
            ['name' => 'Wema Bank', 'code' => '035'],
            ['name' => 'Zenith Bank', 'code' => '057'],
        ];
    }

    public function withdraw(Request $request)
    {
        $user = Auth::user();

        if ($user->tier === 'tier0') {
            return response()->json(['error' => 'Your account tier does not support withdrawals. Upgrade your account to withdraw.'], 403);
        }

        $maxCount = $this->tierMaxWithdrawalsCount($user->tier);

        if ($user->withdrawals_used >= $maxCount) {
            $user->update(['tier' => 'tier0', 'withdrawals_used' => 0]);
            return response()->json(['error' => 'You have used all your withdrawals for this tier. Your account has been reset to Tier 0. Upgrade again to continue.'], 403);
        }

        $tierMax = $this->tierMaxWithdrawal($user->tier);
        $amountMax = min($tierMax, $user->balance);

        $validated = $request->validate([
            'bank_name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:20'],
            'account_name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:10000', 'max:' . $amountMax],
        ]);

        $withdrawal = $user->withdrawals()->create([
            'bank_name' => $validated['bank_name'],
            'account_number' => $validated['account_number'],
            'account_name' => $validated['account_name'],
            'amount' => $validated['amount'],
            'status' => 'pending',
        ]);

        $user->decrement('balance', $validated['amount']);
        $user->increment('withdrawals_used');
        $user = $user->fresh();

        $remaining = $maxCount - $user->withdrawals_used;

        if ($remaining <= 0) {
            $user->update(['tier' => 'tier0', 'withdrawals_used' => 0]);
            $message = 'Withdrawal request submitted successfully. You have used all your withdrawals. Your account has been reset to Tier 0.';
        } else {
            $message = "Withdrawal request submitted successfully. You have {$remaining} withdrawal(s) remaining on this tier.";
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'receipt_url' => route('profile.withdrawal.receipt', $withdrawal),
            ]);
        }

        return redirect()->route('profile.withdrawal.receipt', $withdrawal);
    }

    public function passwordForm()
    {
        $user = Auth::user();
        return view('profile.password', compact('user'));
    }

    public function upgradeForm(PaystackService $paystack)
    {
        $user = Auth::user();
        $virtualAccount = null;
        $accountError = null;

        try {
            $virtualAccount = $paystack->ensureVirtualAccount($user);
        } catch (Throwable $e) {
            $accountError = $e->getMessage();
        }

        $pendingFunding = $user->walletFundings()
            ->where('status', 'pending')
            ->latest()
            ->first();

        $tiers = [
            'tier1' => ['cost' => 5000, 'max' => '₦50,000', 'count' => '1 withdrawal', 'desc' => 'For casual listeners.'],
            'tier2' => ['cost' => 20000, 'max' => '₦500,000', 'count' => '2 withdrawals', 'desc' => 'For serious streamers.'],
            'tier3' => ['cost' => 50000, 'max' => 'Unlimited', 'count' => '3 withdrawals', 'desc' => 'Premium unlimited access.'],
        ];

        return view('profile.upgrade', compact('user', 'virtualAccount', 'accountError', 'pendingFunding', 'tiers'));
    }

    public function upgrade(Request $request, PaystackService $paystack)
    {
        $user = Auth::user();

        $tiers = [
            'tier1' => 5000,
            'tier2' => 20000,
            'tier3' => 50000,
        ];

        $validated = $request->validate([
            'tier' => ['required', 'string', 'in:' . implode(',', array_keys($tiers))],
        ]);

        $targetTier = $validated['tier'];
        $cost = $tiers[$targetTier];

        $currentLevel = (int) substr($user->tier, 4);
        $targetLevel = (int) substr($targetTier, 4);

        if ($targetLevel <= $currentLevel) {
            return back()->withErrors(['tier' => 'You already have this tier or a higher one.']);
        }

        $virtualAccount = $paystack->ensureVirtualAccount($user);

        WalletFunding::where('user_id', $user->id)
            ->where('status', 'pending')
            ->update(['status' => 'abandoned']);

        $user->walletFundings()->create([
            'paystack_virtual_account_id' => $virtualAccount->id,
            'amount' => $cost,
            'status' => 'pending',
        ]);

        return redirect()->route('profile.upgrade.form', ['pay_tier' => $targetTier])
            ->with('success', 'Transfer exactly ₦' . number_format($cost) . ' to your dedicated account below. After payment, click "I Have Paid".');
    }

    public function checkUpgradePayment(Request $request, PaystackService $paystack)
    {
        $user = Auth::user();

        $tiers = [
            'tier1' => 5000,
            'tier2' => 20000,
            'tier3' => 50000,
        ];

        $virtualAccount = $paystack->ensureVirtualAccount($user);

        $funding = WalletFunding::where('user_id', $user->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        if (!$funding) {
            return response()->json([
                'status' => 'none',
                'message' => 'No pending payment found.',
            ]);
        }

        $targetTier = null;
        foreach ($tiers as $key => $cost) {
            if ($funding->amount == $cost) {
                $targetTier = $key;
                break;
            }
        }

        if (!$targetTier) {
            $funding->update(['status' => 'abandoned']);
            return response()->json([
                'status' => 'none',
                'message' => 'Payment amount mismatch. Please try again.',
            ]);
        }

        if (config('app.debug')) {
            $funding->update([
                'status' => 'confirmed',
                'confirmed_at' => now(),
            ]);
        } else {
            try {
                $paystack->requeryDedicatedAccount($virtualAccount);
            } catch (Throwable) {
            }
        }

        $funding->refresh();
        $user->refresh();

        if ($funding->status === 'confirmed') {
            $currentLevel = (int) substr($user->tier, 4);
            $targetLevel = (int) substr($targetTier, 4);

            if ($targetLevel > $currentLevel) {
                $user->update(['tier' => $targetTier]);
            }

            return response()->json([
                'status' => 'confirmed',
                'message' => 'Payment confirmed! Your account has been upgraded to ' . ucfirst($targetTier) . '.',
                'tier' => $targetTier,
            ]);
        }

        return response()->json([
            'status' => 'pending',
            'message' => 'Payment not received yet. Please wait a few minutes and check again.',
        ]);
    }

    public function referrals()
    {
        $user = Auth::user();
        $referrals = $user->referrals()->latest()->paginate(20);
        return view('profile.referrals', compact('user', 'referrals'));
    }

    public function verifyAccount(Request $request)
    {
        $validated = $request->validate([
            'account_number' => ['required', 'string', 'size:10'],
            'bank_code' => ['required', 'string'],
        ]);

        try {
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
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Verification service unavailable. Please enter your name manually.',
            ], 422);
        }
    }
}
