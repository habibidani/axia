<?php
header('Content-Type: text/plain');

echo "=== PHP $_SERVER Debug ===\n\n";

$keys = [
    'REMOTE_ADDR',
    'HTTP_X_FORWARDED_FOR',
    'HTTP_X_FORWARDED_PROTO',
    'HTTP_X_FORWARDED_HOST',
    'HTTP_X_FORWARDED_PORT',
    'HTTPS',
    'SERVER_PROTOCOL',
    'REQUEST_METHOD',
    'REQUEST_URI',
];

foreach ($keys as $key) {
    $value = $_SERVER[$key] ?? 'NOT SET';
    echo "$key = $value\n";
}

echo "\n=== Test Laravel Bootstrap ===\n";

try {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make('Illuminate\Contracts\Console\Kernel');
    $kernel->bootstrap();
    
    echo "Laravel: OK\n";
    echo "APP_ENV: " . config('app.env') . "\n";
    echo "APP_DEBUG: " . (config('app.debug') ? 'true' : 'false') . "\n";
    
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
