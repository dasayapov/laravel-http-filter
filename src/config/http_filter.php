<?php
return [


    /**
     * Стоп слова в адресе, для которых блокировать ip
     */
    'stop_words' => [
        '/.env',
        '/.git',
        '/wp-includes/',
        '/wp-config.php',
        '/autodiscover/autodiscover.json',
    ],

    // Время блокировки
    'stop_words_expiration_time' => 3600,

    /**
     * Через сколько 404х запросов в минуту блокировать
     */
    'not_fount_per_minute' => 10,

    /**
     * Через сколько запросов в минуту блокировать
     */
    'requests_per_minute' => 100,

    /**
     * Какой код ответа при блокировки
     */
    'blocked_http_code' => 403,

    /**
     * На сколько блокировать
     */
    'block_expiration_time' => 3600 * 24,

    /**
     * Использовать кэш для накопления запросов в течении минуты
     * С помощью php artisan http-filter:save-requests запросы сохраняются в базу
     */
    'use_cache' => 0,
];
