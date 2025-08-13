<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $fillable = ['name', 'duration_months', 'price'];


    public function services()
    {
        return $this->belongsToMany(Service::class, 'campaign_service');
    }
}
