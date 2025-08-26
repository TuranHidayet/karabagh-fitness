<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = ['name', 'duration', 'duration_days', 'price', 'total_entries', 'shift_start', 'shift_end'];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
