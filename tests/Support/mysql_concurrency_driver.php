<?php

require __DIR__.'/../../vendor/autoload.php';

$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Profile;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;

function runWorkers(int $userId, array $unitIdsA, ?array $unitIdsB = null): array
{
    $unitIdsB ??= $unitIdsA;
    $root = __DIR__;
    $token = uniqid('sprint1_', true);
    $dir = sys_get_temp_dir();
    $readyA = "$dir\\{$token}_a.ready";
    $readyB = "$dir\\{$token}_b.ready";
    $resultA = "$dir\\{$token}_a.json";
    $resultB = "$dir\\{$token}_b.json";
    $gate = "$dir\\{$token}.gate";
    $php = PHP_BINARY;
    $worker = $root.'\\mysql_concurrency_worker.php';
    $unitsA = implode(',', $unitIdsA);
    $unitsB = implode(',', $unitIdsB);
    $cmd = static fn (string $units, string $ready, string $result): string =>
        escapeshellarg($php).' '.escapeshellarg($worker).' '.escapeshellarg((string) $userId).' '.escapeshellarg($units).' '.escapeshellarg($ready).' '.escapeshellarg($gate).' '.escapeshellarg($result);
    $pipesA = $pipesB = [];
    $procA = proc_open($cmd($unitsA, $readyA, $resultA), [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipesA);
    $procB = proc_open($cmd($unitsB, $readyB, $resultB), [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipesB);
    for ($i = 0; $i < 300 && (!file_exists($readyA) || !file_exists($readyB)); $i++) usleep(10000);
    touch($gate);
    proc_close($procA);
    proc_close($procB);
    $out = [json_decode(file_get_contents($resultA), true), json_decode(file_get_contents($resultB), true)];
    foreach ([$readyA, $readyB, $resultA, $resultB, $gate] as $file) @unlink($file);
    return $out;
}

function seedScenario(): array
{
    $user = User::factory()->create(['role' => 'user', 'is_active' => true]);
    Profile::create(['user_id' => $user->id]);
    $units = collect(range(1, 3))->map(fn (int $n) => Unit::create(['unit_code' => 'CON-'.$n, 'name' => 'Concurrency Artifact', 'description' => 'Test', 'rental_price' => 10000, 'rarity' => 'Rare', 'condition' => 'GOOD', 'status' => 'AVAILABLE']));
    return [$user->id, $units->pluck('id')->all()];
}

Artisan::call('migrate:fresh', ['--force' => true]);
[$userId, $units] = seedScenario();
$sameUnit = runWorkers($userId, [$units[0]]);
$sameUnitSuccesses = count(array_filter($sameUnit, fn (array $result): bool => $result['ok']));

Artisan::call('migrate:fresh', ['--force' => true]);
[$userId, $units] = seedScenario();
app(App\Services\RentalWorkflowService::class)->request($userId, [$units[0]]);
$quota = runWorkers($userId, [$units[1]], [$units[2]]);

echo json_encode([
    'same_unit' => ['results' => $sameUnit, 'successes' => $sameUnitSuccesses],
    'one_quota_left' => ['results' => $quota, 'successes' => count(array_filter($quota, fn (array $result): bool => $result['ok']))],
], JSON_PRETTY_PRINT).PHP_EOL;
