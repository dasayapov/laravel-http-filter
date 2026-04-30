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
        $allRequests = [];
        $maxCount = 100;
        for ($i = 0; $i < $maxCount; $i++) {
            $cacheKey = 'http_filter_requests_' . $i;

            $data = Cache::get($cacheKey, []);

            if (empty($data)) continue;

            // Очистить кэш
            Cache::forget($cacheKey);

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
                $ipStat['not_found'] > config('http_filter.not_fount_per_minute')
                ||
                $ipStat['requests'] > config('http_filter.requests_per_minute')
            ) {

                $data['is_blocked'] = 1;
                $data['blocked_at'] = now();
                $data['block_expire_at'] = now()->addSeconds(config('http_filter.block_expiration_time'));

                $ip->update($data);
            }
        }
    }
}
