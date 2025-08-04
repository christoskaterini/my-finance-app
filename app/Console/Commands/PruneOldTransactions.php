<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PruneOldTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transactions:prune-old';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permanently delete soft-deleted transactions older than 3 months';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Pruning old soft-deleted transactions...');

        $cutoffDate = now()->subMonths(3);

        try {
            $deletedCount = Transaction::onlyTrashed()->where('deleted_at', '<', $cutoffDate)->forceDelete();

            if ($deletedCount > 0) {
                $this->info("Successfully pruned {$deletedCount} old transactions.");
                Log::info("Successfully pruned {$deletedCount} old transactions.");
            } else {
                $this->info('No old transactions to prune.');
            }
        } catch (\Exception $e) {
            $this->error('An error occurred while pruning old transactions.');
            Log::error('Error pruning old transactions: ' . $e->getMessage());
        }

        return 0;
    }
}