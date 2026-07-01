<?php

namespace App\Services;

use App\Models\PaystackVirtualAccount;
use App\Models\User;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PaystackService
{
    private string $baseUrl = 'https://api.paystack.co';

    public function ensureVirtualAccount(User $user): PaystackVirtualAccount
    {
        $existing = $user->paystackVirtualAccount;

        if ($existing && $existing->account_number) {
            return $existing;
        }

        $customer = $this->createCustomer($user);
        $account = $this->createDedicatedAccount($customer['customer_code']);

        return PaystackVirtualAccount::updateOrCreate(
            ['user_id' => $user->id],
            [
                'customer_code' => $customer['customer_code'],
                'dedicated_account_id' => $account['id'] ?? null,
                'bank_name' => $account['bank']['name'] ?? null,
                'bank_slug' => $account['bank']['slug'] ?? null,
                'account_number' => $account['account_number'],
                'account_name' => $account['account_name'] ?? null,
                'currency' => $account['currency'] ?? 'NGN',
                'active' => (bool) ($account['active'] ?? true),
                'assigned' => (bool) ($account['assigned'] ?? false),
                'raw_payload' => $account,
            ]
        );
    }

    public function requeryDedicatedAccount(PaystackVirtualAccount $account): array
    {
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

        $response = $this->client()->post('/customer', [
            'email' => $user->email,
            'first_name' => $nameParts[0] ?? $user->name,
            'last_name' => $nameParts[1] ?? $nameParts[0] ?? $user->name,
            'phone' => $user->phone,
        ]);

        return $this->decode($response->json(), 'Could not create Paystack customer.');
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

    private function client(): PendingRequest
    {
        $secretKey = config('services.paystack.secret_key');

        if (!$secretKey) {
            throw new RuntimeException('PAYSTACK_SECRET_KEY is not configured.');
        }

        return Http::withToken($secretKey)
            ->acceptJson()
            ->asJson()
            ->baseUrl($this->baseUrl)
            ->timeout(30);
    }

    private function decode(?array $payload, string $fallbackMessage): array
    {
        if (($payload['status'] ?? false) !== true) {
            throw new RuntimeException($payload['message'] ?? $fallbackMessage);
        }

        return $payload['data'] ?? [];
    }
}
