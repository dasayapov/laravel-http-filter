<?php

namespace Dasayapov\LaravelHttpFilter\Http\Middleware;

use Closure;
use Dasayapov\LaravelHttpFilter\Models\HttpFilterIp;
use Dasayapov\LaravelHttpFilter\Models\HttpFilterRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HttpFilterBeforeRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Добавить лог запросов
//        try {

            $requestData = [
                'method'        => substr($request->getMethod(), 0, 10),
                'domain'        => substr($request->getHost(), 0, 255),
                'url'           => substr($request->getRequestUri(), 0, 255),
                'ip'            => $request->getClientIp(),
                'user_agent'    => substr($request->userAgent(), 0, 255),
                'created_at'    => now()->format('Y-m-d H:i:s'),
            ];

            // Проверить блокировку IP и сохранить в лог запросов
            $ipAddress = $request->getClientIp();
            $ip = HttpFilterIp::firstOrCreate(['ip' => $ipAddress], ['ip' => $ipAddress]);
            if ($ip->is_blocked) {

                if ($ip->block_expire_at->gt(now())) {
                    $requestData['code'] = config('http_filter.blocked_http_code');

                    try {
                        HttpFilterRequest::create($requestData);
                    } catch (\Throwable $e) {
                        Log::error($e->getMessage());
                    }
                    abort(config('http_filter.blocked_http_code'));
                } else {
                    $ip->update([
                        'is_blocked'        => 0,
                        'blocked_at'        => null,
                        'block_expire_at'   => null,
                    ]);
                }
            }

            $request->attributes->set('http_filter_request', $requestData);
            $request->attributes->set('http_filter_request_time', microtime(true));

//        } catch (\Throwable $e) {
//            Log::error($e->getMessage());
//        }

        return $next($request);
    }
}
