<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PremiumPayment extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
