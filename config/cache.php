<?php

use Illuminate\Support\Str;

return [
    'default' => env('CACHE_STORE', 'database'),
    'stores' => [
        'array' => ['driver' => 'array'],
        'file' => ['driver' => 'file', 'path' => storage_path('framework/cache/data')],
        'database' => ['driver' => 'database', 'table' => 'cache', 'connection' => null, 'lock_connection' => null],
        'redis' => ['driver' => 'redis', 'connection' => 'default', 'lock_connection' => 'default'],
    ],
    'prefix' => env('CACHE_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_cache_'),
];
