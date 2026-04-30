<?php

namespace Dasayapov\LaravelHttpFilter;

use Dasayapov\LaravelHttpFilter\Console\Commands\HttpFilterIpInfo;
use Dasayapov\LaravelHttpFilter\Console\Commands\HttpFilterSaveRequests;
use Illuminate\Support\ServiceProvider;

class HttpFilterProvider extends ServiceProvider
{
    public function boot()
    {
        // Скопировать конфиг в общую папку для настроек
        if (!file_exists(base_path('config/http_filter.php'))) {
            $this->publishes([
                __DIR__ . '/config/http_filter.php' => base_path('config/http_filter.php'),
            ], 'http-filter-config');
        }

        // Применить миграции
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                HttpFilterSaveRequests::class,
                HttpFilterIpInfo::class,
            ]);
        }
    }
}
