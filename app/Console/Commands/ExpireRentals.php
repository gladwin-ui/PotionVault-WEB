<?php

namespace App\Console\Commands;

use App\Services\RentalWorkflowService;
use Illuminate\Console\Command;

class ExpireRentals extends Command
{
    protected $signature = 'potionvault:expire-rentals';
    protected $description = 'Expire approved rentals that missed their 24-hour pickup window';
    public function handle(RentalWorkflowService $workflow): int { $this->info('Expired '.$workflow->expireApproved().' rental(s).'); return self::SUCCESS; }
}
