<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{

    protected $fillable = [
        'name',
    ];

    public function campaigns()
    {
        return $this->belongsToMany(Campaign::class, 'campaign_service');
    }
}
