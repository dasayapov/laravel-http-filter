<?php

namespace Dasayapov\LaravelHttpFilter\Models;

use Illuminate\Database\Eloquent\Model;

class HttpFilterIp extends Model
{
    protected $fillable = [
        'ip', 'requests_count', 'is_blocked', 'blocked_at', 'block_expire_at',
    ];

    protected $casts = [
        'is_blocked'        => 'boolean',
        'blocked_at'        => 'datetime',
        'block_expire_at'   => 'datetime',
    ];

    public function block($expireAt)
    {
        $this->update([
            'is_blocked' => 1,
            'blocked_at' => now(),
            'block_expire_at' => $expireAt,
        ]);
    }

    public function unblock()
    {
        $this->update([
            'is_blocked'        => 0,
            'blocked_at'        => null,
            'block_expire_at'   => null,
        ]);
    }



}
