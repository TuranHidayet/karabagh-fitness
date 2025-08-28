<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogEntry extends Model
{
    protected $table = 'logs';

    protected $fillable = [
        'message',   // Hadisənin izahı (məs: "User silindi")
        'level',     // Log səviyyəsi (info, warning, error və s.)
        'method',    // HTTP method (GET, POST, PUT, DELETE)
        'path',      // Endpoint ünvanı (api/users/5/delete)
        'ip',        // İstifadəçinin IP ünvanı
        'user_id',   // Əməliyyatı edən istifadəçi (admin və ya adi user)
        'payload',   // Əlavə məlumat (request body və ya silinən user dataları)
        'ua',        // User Agent (browser, Postman və ya mobil tətbiq)
        'created_at' // Logun tarixi 
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
