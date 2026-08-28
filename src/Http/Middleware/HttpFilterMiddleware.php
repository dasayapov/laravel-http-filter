<?php

namespace Dasayapov\LaravelHttpFilter\Http\Middleware;

use Closure;
use Dasayapov\LaravelHttpFilter\Events\HttpFilterBlockedEvent;
use Dasayapov\LaravelHttpFilter\Models\HttpFilterIp;
use Dasayapov\LaravelHttpFilter\Models\HttpFilterRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class HttpFilterMiddleware
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
        // Обработать данные до запроса

        $ipAddress = $request->getClientIp();
        $userAgent = $request->userAgent();
        $abort = false;

        $input = [];
        try {
            $input = $request->input();
        } catch (Throwable $e) {
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        }

        $requestData = [
            'method'        => mb_substr($request->getMethod(), 0, 10),
            'domain'        => mb_substr($request->getHost(), 0, 255),
            'url'           => mb_substr($request->getRequestUri(), 0, 255),
            'input'         => $input,
            'ip'            => $request->getClientIp(),
            'user_agent'    => htmlspecialchars(mb_substr($request->userAgent(), 0, 255)),
            'created_at'    => now()->format('Y-m-d H:i:s'),
        ];

        $ip = HttpFilterIp::firstOrCreate(['ip' => $ipAddress], ['ip' => $ipAddress]);

        // Заблокирован и время не прошло
        if ($ip->is_blocked && $ip->block_expire_at->gt(now())) {
            $abort = true;
        }

        // Заблокирован и время прошло - разблокировать
        elseif ($ip->is_blocked && $ip->block_expire_at->lte(now())) {
            $ip->unblock();
        }

        if (!$abort && config('http_filter.stop_words.enabled')) {
            // Проверить стоп-слова в адресе
            $qpos = mb_strpos($_SERVER['REQUEST_URI'], '?');
            if ($qpos !== false) {
                $url = mb_substr($_SERVER['REQUEST_URI'], 0, $qpos);
            } else {
                $url = $_SERVER['REQUEST_URI'];
            }

            foreach (config('http_filter.stop_words.list') as $stopword) {
                if (str_contains($url, $stopword)) {

                    // Индивидуальное время
                    if (config('http_filter.stop_words.block_expiration_time')) {
                        $ip->block(now()->addSeconds(config('http_filter.stop_words.block_expiration_time')));
                    } else {
                        // Общее время
                        $ip->block(now()->addSeconds(config('http_filter.block_expiration_time')));
                    }

                    $abort = true;
                    // Событие
                    HttpFilterBlockedEvent::dispatch($ip->id, HttpFilterBlockedEvent::TYPE_STOP_WORDS, [
                        'stop_word' => $stopword,
                    ]);
                    break;
                }
            }
        }

        if (!$abort && config('http_filter.user_agents.enabled')) {
            // Проверить user-agent
            foreach (config('http_filter.user_agents.list') as $userAgentKeyword) {
                if (str_contains($userAgent, $userAgentKeyword)) {

                    // Индивидуальное время
                    if (config('http_filter.user_agents.block_expiration_time')) {
                        $ip->block(now()->addSeconds(config('http_filter.user_agents.block_expiration_time')));
                    } else {
                        // Общее время
                        $ip->block(now()->addSeconds(config('http_filter.block_expiration_time')));
                    }

                    $abort = true;

                    // Событие
                    HttpFilterBlockedEvent::dispatch($ip->id, HttpFilterBlockedEvent::TYPE_USER_AGENT, [
                        'user_agent' => $userAgentKeyword,
                    ]);
                    break;
                }
            }
        }

        if ($abort) {
            // Сохранить данные
            $requestData['code'] = config('http_filter.blocked_http_code');

            if (config('http_filter.requests.enabled')) {
                HttpFilterRequest::create($requestData);
            }

            // Счетчик
            $ip->increment('requests_count');

            abort(config('http_filter.blocked_http_code'));
        } else {
            $request->attributes->set('http_filter_request', $requestData);
            $request->attributes->set('http_filter_request_time', microtime(true));
        }

        // ОБработать запрос
        $response = $next($request);

        // Обработать данные после запроса

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
