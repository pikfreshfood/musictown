<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'balance',
        'is_admin',
        'is_premium',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'balance' => 'decimal:2',
        ];
    }

    public function listens()
    {
        return $this->hasMany(Listen::class);
    }

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function premiumPayments()
    {
        return $this->hasMany(PremiumPayment::class);
    }

    public function paystackVirtualAccount()
    {
        return $this->hasOne(PaystackVirtualAccount::class);
    }

    public function walletFundings()
    {
        return $this->hasMany(WalletFunding::class);
    }

    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }
}
