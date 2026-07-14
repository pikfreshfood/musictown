<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'balance',
        'is_admin',
        'is_premium',
        'role',
        'referral_code',
        'referrer_id',
        'tier',
        'withdrawals_used',
    ];

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->referral_code)) {
                $user->referral_code = static::generateReferralCode();
            }

            if (empty($user->phone)) {
                $user->phone = static::generateHiddenPhone();
            }
        });
    }

    public static function generateReferralCode(): string
    {
        $code = Str::upper(Str::random(8));
        while (static::where('referral_code', $code)->exists()) {
            $code = Str::upper(Str::random(8));
        }
        return $code;
    }

    public static function generateHiddenPhone(): string
    {
        do {
            $phone = '0809'.str_pad((string) random_int(0, 9999999), 7, '0', STR_PAD_LEFT);
        } while (static::where('phone', $phone)->exists());

        return $phone;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'phone',
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

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referrals()
    {
        return $this->hasMany(User::class, 'referrer_id');
    }
}
