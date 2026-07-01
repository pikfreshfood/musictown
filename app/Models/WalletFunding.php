<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletFunding extends Model
{
    protected $fillable = [
        'user_id',
        'paystack_virtual_account_id',
        'amount',
        'status',
        'paystack_reference',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'confirmed_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function virtualAccount()
    {
        return $this->belongsTo(PaystackVirtualAccount::class, 'paystack_virtual_account_id');
    }

    public function transaction()
    {
        return $this->hasOne(WalletTransaction::class);
    }
}
