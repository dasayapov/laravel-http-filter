<?php


namespace Dasayapov\LaravelHttpFilter\Console\Commands;

use Dasayapov\LaravelHttpFilter\Models\HttpFilterIp;
use Dasayapov\LaravelHttpFilter\Models\HttpFilterRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class HttpFilterIpInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'http-filter:ip-info {ip} {--unblock} {--block} {--block-time=3600}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Информация об IP, блокировка и разблокировка';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ipAddress = $this->argument('ip');

        $ip = HttpFilterIp::whereIp($ipAddress)->first();

        if (!$ip) {
            $this->error('IP не найден');
            exit;
        }

        if ($this->option('block')) {
            $ip->update([
                'is_blocked'        => 1,
                'blocked_at'        => now(),
                'block_expire_at'   => now()->addSeconds($this->option('block-time')),
            ]);
        }

        if ($this->option('unblock')) {
            $ip->update([
                'is_blocked'        => 0,
                'blocked_at'        => null,
                'block_expire_at'   => null,
            ]);
        }

        $status = $ip->is_blocked ? 'Заблокирован' : 'Не заблокирован';
        $this->info('IP ' . $ipAddress . ' ' . $status);

        if ($ip->is_blocked) {
            $this->info('--unblock - разблокировать');
        } else {
            $this->info('--block - заблокировать');
        }

    }
}
