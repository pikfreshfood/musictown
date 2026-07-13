<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\PaystackService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UserPhoneGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_signup_form_does_not_ask_for_phone(): void
    {
        $this->get(route('signup'))
            ->assertOk()
            ->assertDontSee('name="phone"', false);
    }

    public function test_signup_generates_hidden_phone_number(): void
    {
        $this->post(route('signup.submit'), [
            'name' => 'Hidden Phone User',
            'email' => 'hidden-phone@example.test',
            'password' => 'password',
            'terms' => 'on',
        ])->assertRedirect(route('profile'));

        $user = User::where('email', 'hidden-phone@example.test')->firstOrFail();

        $this->assertMatchesRegularExpression('/^0809\d{7}$/', $user->phone);
        $this->assertArrayNotHasKey('phone', $user->toArray());
    }

    public function test_paystack_flow_generates_phone_for_existing_users_without_one(): void
    {
        config(['services.paystack.secret_key' => 'sk_test_hidden_phone']);

        Http::fake([
            'https://api.paystack.co/customer' => Http::response([
                'status' => true,
                'data' => ['customer_code' => 'CUS_hidden_phone'],
            ]),
            'https://api.paystack.co/dedicated_account' => Http::response([
                'status' => true,
                'data' => [
                    'id' => 12345,
                    'bank' => ['name' => 'Test Bank', 'slug' => 'test-bank'],
                    'account_number' => '1234567890',
                    'account_name' => 'Hidden Phone User',
                    'currency' => 'NGN',
                    'active' => true,
                    'assigned' => true,
                ],
            ]),
        ]);

        $user = User::factory()->create(['name' => 'Hidden Phone User']);
        $user->forceFill(['phone' => null])->save();

        app(PaystackService::class)->ensureVirtualAccount($user);

        $user->refresh();

        $this->assertMatchesRegularExpression('/^0809\d{7}$/', $user->phone);

        Http::assertSent(function ($request) use ($user) {
            return $request->url() === 'https://api.paystack.co/customer'
                && ($request->data()['phone'] ?? null) === '+234'.substr($user->phone, 1);
        });

        Http::assertSent(function ($request) use ($user) {
            return $request->url() === 'https://api.paystack.co/dedicated_account'
                && ($request->data()['phone'] ?? null) === '+234'.substr($user->phone, 1)
                && ($request->data()['first_name'] ?? null) === 'Hidden'
                && ($request->data()['last_name'] ?? null) === 'Phone User';
        });
    }
}
