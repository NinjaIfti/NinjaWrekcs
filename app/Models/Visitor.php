<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'os',
        'country',
        'page_visited',
        'last_visit_at',
        'visit_count',
    ];

    protected $casts = [
        'last_visit_at' => 'datetime',
    ];
}









