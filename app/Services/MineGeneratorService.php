<?php

namespace App\Services;

class MineGeneratorService
{
    /**
     * Generate secure server-side random mine locations
     *
     * @param int $minesCount
     * @param int $gridSize
     * @return array
     */
    public function generateMinePositions(int $minesCount, int $gridSize = 25): array
    {
        if ($minesCount <= 0 || $minesCount >= $gridSize) {
            throw new \InvalidArgumentException("Invalid mine count ($minesCount) for grid size $gridSize.");
        }

        $minePositions = [];
        while (count($minePositions) < $minesCount) {
            $pos = random_int(0, $gridSize - 1);
            if (!in_array($pos, $minePositions, true)) {
                $minePositions[] = $pos;
            }
        }

        sort($minePositions);
        return $minePositions;
    }

    /**
     * Calculate dynamic multiplier for Mines game
     * Formula: Product of probability inverse for each safe pick, adjusted for house edge (e.g. 96% RTP)
     *
     * @param int $minesCount
     * @param int $safePicks
     * @param int $gridSize
     * @param float $rtp
     * @return float
     */
    public function calculateMultiplier(int $minesCount, int $safePicks, int $gridSize = 25, float $rtp = 0.96): float
    {
        if ($safePicks <= 0) {
            return 1.00;
        }

        $multiplier = 1.00;
        for ($i = 0; $i < $safePicks; $i++) {
            $remainingTotal = $gridSize - $i;
            $remainingSafe = $gridSize - $minesCount - $i;

            if ($remainingSafe <= 0) {
                break;
            }

            $multiplier *= ($remainingTotal / $remainingSafe);
        }

        $finalMultiplier = $multiplier * $rtp;
        return max(1.01, round($finalMultiplier, 2));
    }
}
