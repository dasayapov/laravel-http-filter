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
        $ipAddress = $request->getClientIp();
        $abort = false;

        $requestData = [
            'method'        => substr($request->getMethod(), 0, 10),
            'domain'        => substr($request->getHost(), 0, 255),
            'url'           => substr($request->getRequestUri(), 0, 255),
            'input'         => $request->input(),
            'ip'            => $request->getClientIp(),
            'user_agent'    => substr($request->userAgent(), 0, 255),
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
                $url = substr($_SERVER['REQUEST_URI'], 0, $qpos);
            } else {
                $url = $_SERVER['REQUEST_URI'];
            }

            foreach (config('http_filter.stop_words.list') as $stopword) {
                if (str_contains($url, $stopword)) {
                    $ip->block(now()->addSeconds(config('http_filter.block_expiration_time')));
                    $abort = true;
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

        return $next($request);
    }
}
