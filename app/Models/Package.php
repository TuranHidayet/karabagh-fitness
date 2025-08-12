<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = ['name', 'duration', 'duration_days', 'price'];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
