<?php

namespace App\Http\Controllers;

use App\Models\WalletFunding;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function index(PaystackService $paystack)
    {
        $user = Auth::user();
        $virtualAccount = $paystack->ensureVirtualAccount($user);
        $pendingFunding = $user->walletFundings()
            ->where('status', 'pending')
            ->latest()
            ->first();
        $transactions = $user->walletTransactions()->latest()->take(10)->get();

        return view('profile.wallet', compact('user', 'virtualAccount', 'pendingFunding', 'transactions'));
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

        return redirect()->route('profile.wallet')->with('success', 'Transfer to the account below, then click "I Have Paid".');
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
                'message' => 'No pending wallet funding request found.',
                'balance' => $user->fresh()->balance,
            ]);
        }

        $paystack->requeryDedicatedAccount($virtualAccount);

        $pendingFunding->refresh();
        $user->refresh();

        if ($pendingFunding->status === 'confirmed') {
            return response()->json([
                'status' => 'confirmed',
                'message' => 'Payment confirmed and wallet credited.',
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
