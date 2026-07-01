<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaystackVirtualAccount extends Model
{
    protected $fillable = [
        'user_id',
        'customer_code',
        'dedicated_account_id',
        'bank_name',
        'bank_slug',
        'account_number',
        'account_name',
        'currency',
        'active',
        'assigned',
        'raw_payload',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'assigned' => 'boolean',
            'raw_payload' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fundings()
    {
        return $this->hasMany(WalletFunding::class);
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }
}
