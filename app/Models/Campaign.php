<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $fillable = ['name', 'description', 'duration_months', 'price', 'services'];

    protected $casts = [
        'services' => 'array', 
    ];

    public function services()
    {
        return $this->belongsToMany(Service::class, 'campaign_service');
    }
}
