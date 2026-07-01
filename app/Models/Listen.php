<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Listen extends Model
{
    protected $fillable = [
        'user_id',
        'song_id',
        'listened_date',
    ];

    protected function casts(): array
    {
        return [
            'listened_date' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function song()
    {
        return $this->belongsTo(Song::class);
    }
}
