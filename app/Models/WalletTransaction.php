<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'paystack_virtual_account_id',
        'wallet_funding_id',
        'paystack_reference',
        'amount',
        'currency',
        'channel',
        'sender_name',
        'sender_bank',
        'sender_account_number',
        'paid_at',
        'raw_payload',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'raw_payload' => 'array',
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

    public function funding()
    {
        return $this->belongsTo(WalletFunding::class, 'wallet_funding_id');
    }
}
