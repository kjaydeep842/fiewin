<?php

namespace App\Services;

use App\Models\AndarBaharBet;
use App\Models\AndarBaharRound;
use App\Models\AndarBaharSetting;
use Illuminate\Support\Facades\DB;

class AndarBaharSettlementService
{
    protected AndarBaharGameService $gameService;

    public function __construct(AndarBaharGameService $gameService)
    {
        $this->gameService = $gameService;
    }

    /**
     * Check and settle past rounds that have passed betting/animation window
     */
    public function settlePendingRounds(): void
    {
        $settings = AndarBaharSetting::getSettings();
        $currentRound = $this->gameService->getCurrentRound();

        $pendingRounds = AndarBaharRound::where('status', '!=', 'settled')
            ->where('period_number', '!=', $currentRound->period_number)
            ->get();

        foreach ($pendingRounds as $round) {
            $this->gameService->settleRound($round);
        }
    }
}
