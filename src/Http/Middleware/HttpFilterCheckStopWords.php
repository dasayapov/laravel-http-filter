<?php

namespace Dasayapov\LaravelHttpFilter\Http\Middleware;

use Closure;
use Dasayapov\LaravelHttpFilter\Models\HttpFilterIp;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HttpFilterCheckStopWords
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Проверить стопслова в адресе
        $ipAddress = $request->getClientIp();

        $qpos = mb_strpos($_SERVER['REQUEST_URI'], '?');
        if ($qpos !== false) {
            $url = substr($_SERVER['REQUEST_URI'], 0, $qpos);
        } else {
            $url = $_SERVER['REQUEST_URI'];
        }

        foreach (config('http_filter.stop_words') as $stopword) {
            if (str_contains($url, $stopword)) {
                $ip = HttpFilterIp::firstOrCreate(['ip' => $ipAddress], ['ip' => $ipAddress]);

                if ($ip->is_blocked) {
                    continue;
                }

                $ip->update([
                    'is_blocked'        => 1,
                    'blocked_at'        => now(),
                    'block_expire_at'   => now()->addSeconds(config('http_filter.stop_words_expiration_time')),
                ]);
            }
        }

        return $next($request);
    }
}
