<?php


namespace Dasayapov\LaravelHttpFilter\Console\Commands;

use Dasayapov\LaravelHttpFilter\Models\HttpFilterIp;
use Dasayapov\LaravelHttpFilter\Models\HttpFilterRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class HttpFilterSaveRequests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'http-filter:save-requests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Сохранение в базу закэшированных запросов';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // С отключенным кэшем или без сохранения не работает
        if (
            !config('http_filter.cache.enabled')
            ||
            !config('http_filter.requests.enabled')
        ) {
            exit;
        }

        $allRequests = [];
        $maxCount = 100;
        for ($i = 0; $i < $maxCount; $i++) {
            $cacheKey = 'http_filter_requests_' . $i;

            $data = Cache::driver(config('http_filter.cache.driver'))->get($cacheKey, []);

            if (empty($data)) continue;

            // Очистить кэш
            Cache::driver(config('http_filter.cache.driver'))->forget($cacheKey);

            // Обработать данные
            foreach ($data as $item) {
                $allRequests[] = $item;
            }
        }

        // Сортировка по возрастанию даты, сначала ранние
        usort($allRequests, function ($a, $b) {
            return strtotime($a['created_at']) > strtotime($b['created_at']);
        });

        // Посчитать запросы по IP
        $ipsStats = [];

        foreach ($allRequests as $request) {
            $ip = $request['ip'];

            // Счетчик
            if (isset($ipsStats[$ip])) {
                $ipsStats[$ip]['requests']++;
            } else {
                $ipsStats[$ip] = [
                    'requests' => 1,
                    'not_found' => 0,
                ];
            }

            // 404e
            if (in_array($request['code'], [404, 405])) {
                $ipsStats[$ip]['not_found']++;
            }

            HttpFilterRequest::create($request);
        }

        foreach ($ipsStats as $ipAddress => $ipStat) {
            $ip = HttpFilterIp::firstOrCreate(['ip' => $ipAddress], ['ip' => $ipAddress]);

            // Сохранить количество запросов
            $data = [];

            // Блокировать IP на время: много 404/405 или частые запросы

            if (
                (
                    config('http_filter.requests.not_found.enabled')
                    &&
                    $ipStat['not_found'] > config('http_filter.requests.not_found.per_minute')
                )
                ||
                (
                    config('http_filter.requests.rate_limit.enabled')
                    &&
                    $ipStat['requests'] > config('http_filter.requests.rate_limit.per_minute')
                )
            ) {
                $data['requests_count'] = $ip->requests_count + $ipStat['requests'];
                $data['is_blocked'] = 1;
                $data['blocked_at'] = now();
                $data['block_expire_at'] = now()->addSeconds(config('http_filter.block_expiration_time'));

                $ip->update($data);
            } else {
                $ip->increment('requests_count', $ipStat['requests']);
            }
        }
    }
}
