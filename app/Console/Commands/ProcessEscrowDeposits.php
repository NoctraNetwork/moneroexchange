<?php

namespace App\Console\Commands;

use App\Services\EscrowService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessEscrowDeposits extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'escrow:process-deposits';

    /**
     * The console command description.
     */
    protected $description = 'Process pending escrow deposits and update trade states';

    private EscrowService $escrowService;

    public function __construct(EscrowService $escrowService)
    {
        parent::__construct();
        $this->escrowService = $escrowService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing escrow deposits...');

        try {
            $processed = $this->escrowService->processPendingDeposits();
            
            $this->info("Processed {$processed} deposits successfully.");
            
            Log::info('Escrow deposits processed', ['count' => $processed]);
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to process escrow deposits: ' . $e->getMessage());
            
            Log::error('Failed to process escrow deposits', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Command::FAILURE;
        }
    }
}
