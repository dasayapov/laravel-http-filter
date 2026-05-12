<?php

namespace Dasayapov\LaravelHttpFilter\Http\Middleware;

use Closure;
use Dasayapov\LaravelHttpFilter\Models\HttpFilterRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class HttpFilterAfterRequest
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
        $response = $next($request);

        try {
            $requestData = $request->attributes->get('http_filter_request');
            if ($requestData && config('http_filter.requests.enabled')) {
                // Сохранить данные запроса
                $requestData['time'] = round(microtime(true) - $request->attributes->get('http_filter_request_time'), 2);
                $requestData['code'] = $response->getStatusCode();

                if (config('http_filter.cache.enabled')) {
                    // В рандомный кэш на 3 минуты
                    $cacheKey = 'http_filter_requests_' . mt_rand(1, 100);
                    $data = Cache::get($cacheKey, []);
                    $data[] = $requestData;
                    Cache::driver(config('http_filter.cache_driver'))->put($cacheKey, $data, 180);
                } else {
                    HttpFilterRequest::create($requestData);
                }
            }
        } catch (Throwable $e) {
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        }
        return $response;
    }
}
