<?php

namespace Dasayapov\LaravelHttpFilter\Models;

use Illuminate\Database\Eloquent\Model;

class HttpFilterIp extends Model
{
    protected $fillable = [
        'ip', 'is_blocked', 'blocked_at', 'block_expire_at',
    ];

    protected $casts = [
        'is_blocked'        => 'boolean',
        'blocked_at'        => 'datetime',
        'block_expire_at'   => 'datetime',
    ];

}
