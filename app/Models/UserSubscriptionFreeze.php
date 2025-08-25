<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSubscriptionFreeze extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subscription_id',
        'start_date',
        'end_date',
        'status', 
    ];

    protected $dates = [
        'start_date',
        'end_date',
    ];

    // Freeze aid olduğu subscription
    public function subscription()
    {
        return $this->belongsTo(UserSubscription::class, 'subscription_id');
    }

    // Freeze aid olduğu user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
