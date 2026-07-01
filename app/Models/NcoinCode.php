<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NcoinCode extends Model
{
    protected $fillable = [
        'code',
        'is_used',
        'used_by_user_id',
        'used_at',
        'generated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_used' => 'boolean',
            'used_at' => 'datetime',
        ];
    }
}
