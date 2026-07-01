<?php

namespace App\Http\Controllers;

use App\Models\PaymentAccount;
use App\Models\PremiumPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PremiumController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $paymentAccount = PaymentAccount::where('is_active', true)->first();
        $pendingPayment = PremiumPayment::where('user_id', $user->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        return view('premium.index', compact('user', 'paymentAccount', 'pendingPayment'));
    }

    public function submitPayment(Request $request)
    {
        $user = Auth::user();

        if ($user->is_premium) {
            return redirect()->route('premium.index');
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
        ]);

        PremiumPayment::create([
            'user_id' => $user->id,
            'amount' => $validated['amount'],
            'status' => 'pending',
        ]);

        return redirect()->route('premium.index')->with('success', 'Payment submitted. Waiting for admin approval.');
    }
}
