<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Package;
use App\Models\Campaign;

class UserSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'package_id',
        'campaign_id',
        'start_date',
        'end_date',
    ];

    // Hangi user-ə aid olduğunu göstərir
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Package əlaqəsi
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    // Campaign əlaqəsi
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function freezes()
    {
        return $this->hasMany(UserSubscriptionFreeze::class, 'subscription_id');
    }
}
