<?php

namespace Dasayapov\LaravelHttpFilter\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Request
 *
 * @property int $id
 * @property string|null $url
 * @property string|null $data
 * @property string|null $ip
 * @property string|null $user_agent
 * @property string|null $time
 * @property string $created_at
 * @method static \Illuminate\Database\Eloquent\Builder|HttpFilterRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HttpFilterRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HttpFilterRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder|HttpFilterRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HttpFilterRequest whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HttpFilterRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HttpFilterRequest whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HttpFilterRequest whereIpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HttpFilterRequest whereTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HttpFilterRequest whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HttpFilterRequest whereUserAgent($value)
 * @mixin \Eloquent
 */
class HttpFilterRequest extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'method', 'domain', 'url', 'input', 'ip', 'user_agent', 'time', 'code', 'created_at',
    ];

    protected $casts = [
        'input' => 'json',
    ];
}
