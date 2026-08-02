<?php

namespace App\Console\Commands;

use App\Events\GameStateUpdated;
use App\Events\HistoryUpdated;
use App\Services\CrashRoundService;
use App\Services\JetRoundService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GameEngineWorker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:engine {--once : Run a single tick iteration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enterprise High-Concurrency Real-Time Game Engine Daemon for Jet & Crash';

    /**
     * Execute the console command.
     */
    public function handle(JetRoundService $jetRoundService, CrashRoundService $crashRoundService): int
    {
        $this->info('Starting Enterprise Game Engine Worker (Jet & Crash)...');

        $isOnce = $this->option('once');

        while (true) {
            try {
                // 1. Tick Jet Round Engine
                $jetRound = $jetRoundService->getOrSyncActiveRound();
                $jetState = app(\App\Services\JetGameService::class)->getSynchronizedState(null);
                Cache::put('game:jet:state', $jetState, 5);

                // Broadcast Jet tick if status or multiplier changed
                try {
                    broadcast(new GameStateUpdated('jet', [
                        'round' => [
                            'id' => $jetRound->id,
                            'round_id' => $jetRound->round_id,
                            'status' => $jetRound->status,
                            'crash_multiplier' => round((float)$jetRound->crash_multiplier, 2),
                        ],
                        'seconds_remaining' => $jetState['seconds_remaining'],
                        'current_multiplier' => $jetState['current_multiplier'],
                        'server_timestamp' => time(),
                    ]))->toOthers();
                } catch (\Throwable $e) { /* Broadcast driver silent catch */ }

                // 2. Tick Crash Round Engine
                $crashRound = $crashRoundService->getOrSyncActiveRound();
                $crashState = app(\App\Services\CrashGameService::class)->getSynchronizedState(null);
                Cache::put('game:crash:state', $crashState, 5);

                // Broadcast Crash tick
                try {
                    broadcast(new GameStateUpdated('crash', [
                        'round' => [
                            'id' => $crashRound->id,
                            'round_id' => $crashRound->round_id,
                            'status' => $crashRound->status,
                            'crash_multiplier' => round((float)$crashRound->crash_multiplier, 2),
                        ],
                        'seconds_remaining' => $crashState['seconds_remaining'],
                        'current_multiplier' => $crashState['current_multiplier'],
                        'server_timestamp' => time(),
                    ]))->toOthers();
                } catch (\Throwable $e) { /* Broadcast driver silent catch */ }

            } catch (\Throwable $e) {
                Log::error('GameEngineWorker Loop Exception: ' . $e->getMessage());
            }

            if ($isOnce) {
                break;
            }

            // Sleep 500ms for smooth 2Hz engine ticks
            usleep(500000);
        }

        return 0;
    }
}
