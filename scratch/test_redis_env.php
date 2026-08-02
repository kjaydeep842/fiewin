<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

echo "=== CACHE & REDIS TEST ===\n";
echo "Cache Driver: " . config('cache.default') . "\n";
echo "Queue Driver: " . config('queue.default') . "\n";

try {
    Cache::put('test_cache_key', 'Cache Working!', 10);
    echo "Cache Get: " . Cache::get('test_cache_key') . "\n";
} catch (\Throwable $e) {
    echo "Cache Error: " . $e->getMessage() . "\n";
}

try {
    Redis::set('test_redis_key', 'Redis Connected!');
    echo "Redis Get: " . Redis::get('test_redis_key') . "\n";
} catch (\Throwable $e) {
    echo "Redis Exception: " . $e->getMessage() . "\n";
}
