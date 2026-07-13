<?php

namespace App\Http\Controllers;

use App\Models\NcoinPayment;
use App\Models\PaymentAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NcoinController extends Controller
{
    public function payForm()
    {
        $user = Auth::user();

        $paymentAccount = PaymentAccount::where('is_active', true)->first();
        $pendingNcoin = NcoinPayment::where('user_id', $user->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        $telegramUsername = $paymentAccount?->telegram_username;

        return view('premium.pay-ncoin', compact('user', 'paymentAccount', 'pendingNcoin', 'telegramUsername'));
    }

    public function submitPayment(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $path = $request->file('proof')->store('ncoin-proofs', 'public');

        NcoinPayment::create([
            'user_id' => $user->id,
            'amount' => $validated['amount'],
            'proof_path' => $path,
            'status' => 'pending',
        ]);

        return redirect()->route('premium.payment-submitted');
    }

    public function submitted()
    {
        $paymentAccount = PaymentAccount::where('is_active', true)->first();
        $telegramUsername = $paymentAccount?->telegram_username;

        return view('premium.payment-submitted', compact('telegramUsername'));
    }
}
