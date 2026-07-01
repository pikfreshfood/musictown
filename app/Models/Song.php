<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    protected $fillable = [
        'title',
        'artist',
        'duration',
        'audio_url',
    ];

    public function listens()
    {
        return $this->hasMany(Listen::class);
    }
}
