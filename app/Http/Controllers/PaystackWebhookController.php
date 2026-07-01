<?php

namespace App\Http\Controllers;

use App\Models\PaystackVirtualAccount;
use App\Models\WalletFunding;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaystackWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('x-paystack-signature');
        $secret = config('services.paystack.secret_key');

        if (!$secret || !$signature || !hash_equals(hash_hmac('sha512', $payload, $secret), $signature)) {
            return response('Invalid signature', 401);
        }

        $event = $request->input('event');
        $data = $request->input('data', []);

        if ($event !== 'charge.success') {
            return response('Ignored', 200);
        }

        $authorization = $data['authorization'] ?? [];
        $channel = $authorization['channel'] ?? $data['channel'] ?? null;

        if ($channel !== 'dedicated_nuban') {
            return response('Ignored', 200);
        }

        $reference = $data['reference'] ?? null;
        $receiverAccount = $authorization['receiver_bank_account_number'] ?? null;
        $amount = ((int) ($data['amount'] ?? 0)) / 100;

        if (!$reference || !$receiverAccount || $amount <= 0) {
            Log::warning('Incomplete Paystack DVA webhook payload.', ['payload' => $data]);
            return response('Incomplete payload', 422);
        }

        DB::transaction(function () use ($data, $authorization, $reference, $receiverAccount, $amount, $channel) {
            if (WalletTransaction::where('paystack_reference', $reference)->exists()) {
                return;
            }

            $virtualAccount = PaystackVirtualAccount::where('account_number', $receiverAccount)
                ->lockForUpdate()
                ->first();

            if (!$virtualAccount) {
                Log::warning('Paystack DVA webhook for unknown account.', [
                    'reference' => $reference,
                    'account_number' => $receiverAccount,
                ]);
                return;
            }

            $user = $virtualAccount->user()->lockForUpdate()->first();
            $funding = WalletFunding::where('user_id', $user->id)
                ->where('paystack_virtual_account_id', $virtualAccount->id)
                ->where('status', 'pending')
                ->where('amount', $amount)
                ->oldest()
                ->lockForUpdate()
                ->first();

            $transaction = WalletTransaction::create([
                'user_id' => $user->id,
                'paystack_virtual_account_id' => $virtualAccount->id,
                'wallet_funding_id' => $funding?->id,
                'paystack_reference' => $reference,
                'amount' => $amount,
                'currency' => $data['currency'] ?? 'NGN',
                'channel' => $channel,
                'sender_name' => $authorization['sender_name'] ?? null,
                'sender_bank' => $authorization['sender_bank'] ?? null,
                'sender_account_number' => $authorization['sender_bank_account_number'] ?? null,
                'paid_at' => isset($data['paid_at']) ? Carbon::parse($data['paid_at']) : now(),
                'raw_payload' => $data,
            ]);

            $user->increment('balance', $amount);

            if ($funding) {
                $funding->update([
                    'status' => 'confirmed',
                    'paystack_reference' => $reference,
                    'confirmed_at' => now(),
                ]);

                $transaction->update(['wallet_funding_id' => $funding->id]);
            }
        });

        return response('OK', 200);
    }
}
