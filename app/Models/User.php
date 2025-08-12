<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Models\Role;
use Filament\Panel;
use Carbon\Carbon;


class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
         'first_name',
        'last_name',
        'birth_date',
        'phone',
        'email',
        'gender',
        'guardian_name',
        'guardian_birth_date',
        'card_id',
        'password',
        'package_id',
        'campaign_id',
        'start_date',
        'end_date',
        'promo_code',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected $guard_name = 'web';
    
    // Bu user-in təyin etdiyi user-lər (əgər trainerdirsə)
    public function assignedUsers() {
        return $this->belongsToMany(User::class, 'trainer_user', 'trainer_id', 'user_id');
    }

    // Bu user-in təyin olunduğu trainer-lər
    public function trainers() {
        return $this->belongsToMany(User::class, 'trainer_user', 'user_id', 'trainer_id');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasRole('admin');
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function setStartDateAttribute($value)
    {
        $this->attributes['start_date'] = $value;

        if ($this->package) {
            $start = Carbon::parse($value);

            switch ($this->package->duration) {
                case 'daily':
                    $end = $start->copy()->addDay();
                    break;

                case 'weekly':
                    $end = $start->copy()->addWeek();
                    break;

                case 'monthly':
                    $end = $start->copy()->addMonth();
                    break;

                case 'yearly':
                    $end = $start->copy()->addYear();
                    break;

                default:
                    $end = null;
            }

            $this->attributes['end_date'] = $end ? $end->format('Y-m-d') : null;
        }
    }
}