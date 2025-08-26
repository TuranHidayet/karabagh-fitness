<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\UserSubscription;
use Carbon\Carbon;
use App\Models\User;

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

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function subscription()
    {
        return $this->belongsTo(UserSubscription::class, 'subscription_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}