# dasayapov/laravel-http-filter

Мониторинг и фильтрация трафика

## Documentation

### Выполнить

`composer require dasayapov/laravel-http-filter`

Добавить в config/app.php

`Dasayapov\LaravelHttpFilter\HttpFilterProvider::class,`

Или в bootstrap/providers.php

`Dasayapov\LaravelHttpFilter\HttpFilterProvider::class,`

Создать файл с настройками

`php artisan vendor:publish --tag http-filter-config`

## Добавить cron
php artisan http-filter:save-requests

## Проверка IP
php artisan http-filter:ip-info {ip} {--unblock} {--block} {--block-time=3600}

## Добавить middleware
`HttpFilterMiddleware::class,`

## События
### HttpFilterBlockedEvent - IP заблокирован
Создать слушателя

php artisan make:listener HttpFilterBlockedListener

В файл AppServiceProvider - boot() добавить 
`Event::listen(HttpFilterBlockedEvent::class, HttpFilterBlockedListener::class);`

```php
public function handle(HttpFilterBlockedEvent $event): void
{
    $httpFilterIp = HttpFilterIp::find($event->httpFilterIpId);
}
```

## История обновлений

`2.3.0` Мелкие правки

`2.0.1` Мелкие правки

`2.0.0` Добавлено сохранение параметров запроса

`1.0.0` Первая версия
