<?php

namespace App\Console\Commands;

use App\Services\LedgerService;
use Illuminate\Console\Command;

class RebuildAccountLedgerCommand extends Command
{
    protected $signature = 'ledger:rebuild';

    protected $description = 'Rebuild the account ledger from historical deposits and bills';

    public function handle(LedgerService $ledgerService): int
    {
        $this->info('Rebuilding account ledger…');

        $count = $ledgerService->rebuild();

        $this->info("Ledger rebuilt from {$count} financial events.");

        return self::SUCCESS;
    }
}
