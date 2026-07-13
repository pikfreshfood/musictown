<?php

namespace App\Services;

use App\Models\PaystackVirtualAccount;
use App\Models\User;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PaystackService
{
    private string $baseUrl = 'https://api.paystack.co';

    public function ensureVirtualAccount(User $user, bool $forceNew = false): PaystackVirtualAccount
    {
        $existing = $user->paystackVirtualAccount;

        if ($existing && $existing->account_number && !$forceNew) {
            return $existing;
        }

        if ($existing && $existing->account_number && !$existing->assigned) {
            try {
                $requery = $this->requeryDedicatedAccount($existing);
                return $this->persistVirtualAccount($user, ['customer_code' => $existing->customer_code], $requery, $existing);
            } catch (RuntimeException $exception) {
            }
        }

        $customer = $existing?->customer_code
            ? $this->updateCustomer($existing->customer_code, $user)
            : $this->createCustomer($user);

        $account = $this->createDedicatedAccount($customer['customer_code']);

        return $this->persistVirtualAccount($user, $customer, $account, $existing);
    }

    private function persistVirtualAccount(User $user, array $customer, array $account, ?PaystackVirtualAccount $existing = null): PaystackVirtualAccount
    {
        return PaystackVirtualAccount::updateOrCreate(
            ['user_id' => $user->id],
            [
                'customer_code' => $customer['customer_code'] ?? $existing?->customer_code,
                'dedicated_account_id' => $account['id'] ?? $existing?->dedicated_account_id,
                'bank_name' => $account['bank']['name'] ?? $existing?->bank_name,
                'bank_slug' => $account['bank']['slug'] ?? $existing?->bank_slug,
                'account_number' => $account['account_number'] ?? $existing?->account_number,
                'account_name' => $account['account_name'] ?? $existing?->account_name,
                'currency' => $account['currency'] ?? $existing?->currency ?? 'NGN',
                'active' => (bool) ($account['active'] ?? $existing?->active ?? true),
                'assigned' => (bool) ($account['assigned'] ?? $existing?->assigned ?? false),
                'raw_payload' => $account ?: $existing?->raw_payload,
            ]
        );
    }

    public function requeryDedicatedAccount(PaystackVirtualAccount $account): array
    {
        if (!$account->account_number || !$account->bank_slug) {
            return [];
        }

        $response = $this->client()->get('/dedicated_account/requery', [
            'account_number' => $account->account_number,
            'provider_slug' => $account->bank_slug,
            'date' => now()->toDateString(),
        ]);

        return $this->decode($response->json(), 'Could not requery dedicated account.');
    }

    private function createCustomer(User $user): array
    {
        $nameParts = preg_split('/\s+/', trim($user->name), 2);
        $payload = [
            'email' => $user->email,
            'first_name' => $nameParts[0] ?? $user->name,
            'last_name' => $nameParts[1] ?? $nameParts[0] ?? $user->name,
            'phone' => $this->resolvePhone($user),
        ];

        $response = $this->client()->post('/customer', $payload);

        return $this->decode($response->json(), 'Could not create Paystack customer.');
    }

    private function updateCustomer(string $customerCode, User $user): array
    {
        $nameParts = preg_split('/\s+/', trim($user->name), 2);
        $payload = [
            'first_name' => $nameParts[0] ?? $user->name,
            'last_name' => $nameParts[1] ?? $nameParts[0] ?? $user->name,
            'phone' => $this->resolvePhone($user),
        ];

        $response = $this->client()->put('/customer/'.$customerCode, $payload);

        return $this->decode($response->json(), 'Could not update Paystack customer.');
    }

    private function createDedicatedAccount(string $customerCode): array
    {
        $payload = ['customer' => $customerCode];

        if (config('services.paystack.dva_bank')) {
            $payload['preferred_bank'] = config('services.paystack.dva_bank');
        }

        $response = $this->client()->post('/dedicated_account', $payload);

        return $this->decode($response->json(), 'Could not create Paystack dedicated account.');
    }

    private function resolvePhone(User $user): string
    {
        if ($phone = $this->normalizePhone($user->phone)) {
            return $phone;
        }

        $user->forceFill(['phone' => User::generateHiddenPhone()])->save();

        return $this->normalizePhone($user->phone) ?? $user->phone;
    }

    private function normalizePhone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        $phone = preg_replace('/\s+/', '', $phone);

        if (str_starts_with($phone, '0')) {
            return '+234'.substr($phone, 1);
        }

        return $phone;
    }

    private function client(): PendingRequest
    {
        $secretKey = config('services.paystack.secret_key');

        if (!$secretKey) {
            throw new RuntimeException('PAYSTACK_SECRET_KEY is not configured.');
        }

        $request = Http::withToken($secretKey)
            ->acceptJson()
            ->asJson()
            ->baseUrl($this->baseUrl)
            ->timeout(30);

        if (!config('services.paystack.verify_ssl')) {
            $request = $request->withoutVerifying();
        }

        return $request;
    }

    private function decode(?array $payload, string $fallbackMessage): array
    {
        if (($payload['status'] ?? false) !== true) {
            Log::error('Paystack API error.', [
                'payload' => $payload,
                'fallback_message' => $fallbackMessage,
            ]);

            throw new RuntimeException($payload['message'] ?? $fallbackMessage);
        }

        return $payload['data'] ?? [];
    }
}
