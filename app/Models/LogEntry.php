<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogEntry extends Model
{
    protected $table = 'logs';

    protected $fillable = [
        'message',
        'level',
        'method',
        'path',
        'ip',
        'user_id',
        'payload',
        'ua',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
