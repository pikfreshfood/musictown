<?php

namespace App\Http\Controllers;

use App\Models\WalletFunding;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;
use Throwable;

class WalletController extends Controller
{
    public function index(PaystackService $paystack)
    {
        $user = Auth::user();
        $virtualAccount = null;
        $accountError = null;

        try {
            $virtualAccount = $paystack->ensureVirtualAccount($user);
        } catch (Throwable $exception) {
            $accountError = $exception->getMessage();
        }

        $pendingFunding = $user->walletFundings()
            ->where('status', 'pending')
            ->latest()
            ->first();

        $transactions = $user->walletTransactions()
            ->latest()
            ->take(10)
            ->get();

        return view('profile.wallet', compact('user', 'virtualAccount', 'accountError', 'pendingFunding', 'transactions'));
    }

    public function createFunding(Request $request, PaystackService $paystack)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:100'],
        ]);

        $user = Auth::user();
        $virtualAccount = $paystack->ensureVirtualAccount($user);

        WalletFunding::where('user_id', $user->id)
            ->where('status', 'pending')
            ->update(['status' => 'abandoned']);

        $user->walletFundings()->create([
            'paystack_virtual_account_id' => $virtualAccount->id,
            'amount' => $validated['amount'],
            'status' => 'pending',
        ]);

        return redirect()
            ->route('profile.wallet')
            ->with('success', 'Transfer the exact amount to your dedicated account, then click "I Have Paid".');
    }

    public function checkFunding(PaystackService $paystack)
    {
        $user = Auth::user();
        $virtualAccount = $paystack->ensureVirtualAccount($user);
        $pendingFunding = $user->walletFundings()
            ->where('status', 'pending')
            ->latest()
            ->first();

        if (!$pendingFunding) {
            return response()->json([
                'status' => 'none',
                'message' => 'No pending funding request was found.',
                'balance' => $user->fresh()->balance,
            ]);
        }

        try {
            $requeryResult = $paystack->requeryDedicatedAccount($virtualAccount);

            if (config('app.debug') && !empty($requeryResult)) {
                $pendingFunding->update([
                    'status' => 'confirmed',
                    'confirmed_at' => now(),
                ]);
                $user->increment('balance', $pendingFunding->amount);
            }
        } catch (RuntimeException) {
        }

        $pendingFunding->refresh();
        $user->refresh();

        if ($pendingFunding->status === 'confirmed') {
            return response()->json([
                'status' => 'confirmed',
                'message' => 'Payment confirmed. Your wallet has been credited.',
                'balance' => $user->balance,
            ]);
        }

        return response()->json([
            'status' => 'pending',
            'message' => 'Payment not received yet. Please wait a few minutes and check again.',
            'balance' => $user->balance,
        ]);
    }
}
