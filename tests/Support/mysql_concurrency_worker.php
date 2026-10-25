<?php

require __DIR__.'/../../vendor/autoload.php';

$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$userId = (int) ($argv[1] ?? 0);
$unitIds = array_map('intval', explode(',', $argv[2] ?? ''));
$ready = $argv[3] ?? '';
$gate = $argv[4] ?? '';
$result = $argv[5] ?? '';

file_put_contents($ready, 'ready');
while (!file_exists($gate)) usleep(10000);

try {
    $rental = app(App\Services\RentalWorkflowService::class)->request($userId, $unitIds);
    file_put_contents($result, json_encode(['ok' => true, 'rental_id' => $rental->id]));
} catch (Throwable $exception) {
    file_put_contents($result, json_encode(['ok' => false, 'type' => get_class($exception), 'message' => $exception->getMessage()]));
}
