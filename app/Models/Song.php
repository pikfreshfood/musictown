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
        'image_url',
        'jamendo_id',
        'audius_id',
    ];

    public function listens()
    {
        return $this->hasMany(Listen::class);
    }
}
