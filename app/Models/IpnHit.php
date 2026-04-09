<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpnHit extends Model
{
    protected $table = 'ipn_hits';
    
    protected $fillable = [
        'email',
        'channel',
        'timestamp',
        'data'
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'data' => 'array'
    ];
}