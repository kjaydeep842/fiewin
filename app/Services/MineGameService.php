<?php

namespace App\Services;

use App\Models\Game;
use App\Models\GameBet;
use App\Models\GameMove;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MineGameService
{
    protected WalletService $walletService;
    protected ReferralCommissionService $commissionService;
    protected MineGeneratorService $generatorService;

    public function __construct(
        WalletService $walletService,
        ReferralCommissionService $commissionService,
        MineGeneratorService $generatorService
    ) {
        $this->walletService = $walletService;
        $this->commissionService = $commissionService;
        $this->generatorService = $generatorService;
    }

    /**
     * Start a new Mines Game round
     */
    public function startGame(User $user, Game $game, float $betAmount, int $minesCount): array
    {
        $allowedMines = [1, 3, 5, 10, 15, 20];
        if (!in_array($minesCount, $allowedMines, true)) {
            throw new \InvalidArgumentException("Invalid mines count specified ($minesCount).");
        }

        return DB::transaction(function () use ($user, $game, $betAmount, $minesCount) {
            // Validate wallet balance
            $wallet = $user->wallet ? $user->wallet->fresh() : null;
            if (!$wallet || $wallet->main_balance < $betAmount) {
                throw new \Exception("Insufficient wallet balance. Please deposit to continue.");
            }

            // Deduct user wallet
            $periodNumber = 'MINES_' . now()->format('YmdHis') . '_' . Str::upper(Str::random(6));
            $this->walletService->debit(
                $user->id,
                $betAmount,
                'main',
                'bet',
                "BET_{$periodNumber}",
                "Bet on {$game->name} ({$minesCount} Mines)"
            );

            // Generate secret mine locations securely on server
            $minePositions = $this->generatorService->generateMinePositions($minesCount, 25);

            // Create GameBet DB record
            $bet = GameBet::create([
                'user_id' => $user->id,
                'game_id' => $game->id,
                'period_number' => $periodNumber,
                'bet_amount' => $betAmount,
                'bet_type' => "{$minesCount}_mines",
                'status' => 'pending',
                'multiplier' => 1.00,
                'win_amount' => 0.00,
                'bet_details' => [
                    'game_uuid' => Str::uuid()->toString(),
                    'mines_count' => $minesCount,
                    'mine_positions' => $minePositions,
                    'opened_positions' => [],
                    'current_multiplier' => 1.00,
                    'current_profit' => 0.00,
                    'started_at' => now()->toIso8601String(),
                ],
            ]);

            $freshBalance = number_format($user->wallet->fresh()->main_balance, 2);

            return [
                'success' => true,
                'bet_id' => $bet->id,
                'game_uuid' => $bet->bet_details['game_uuid'],
                'new_balance' => $freshBalance,
                'mines_count' => $minesCount,
                'multiplier' => '1.00',
                'current_profit' => '0.00',
            ];
        });
    }

    /**
     * Process a tile click in Mines Game
     */
    public function revealTile(User $user, int $betId, int $tileIndex): array
    {
        if ($tileIndex < 0 || $tileIndex > 24) {
            throw new \InvalidArgumentException("Invalid tile index ($tileIndex). Must be 0-24.");
        }

        return DB::transaction(function () use ($user, $betId, $tileIndex) {
            $bet = GameBet::where('user_id', $user->id)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->findOrFail($betId);

            $details = $bet->bet_details ?? [];
            $minesCount = (int)($details['mines_count'] ?? 3);
            $minePositions = $details['mine_positions'] ?? [];
            $openedPositions = $details['opened_positions'] ?? [];

            if (in_array($tileIndex, $openedPositions, true)) {
                throw new \Exception("Tile index $tileIndex has already been revealed.");
            }

            // Check if player hit a mine
            if (in_array($tileIndex, $minePositions, true)) {
                // Save game move to DB
                GameMove::create([
                    'game_bet_id' => $bet->id,
                    'tile_index' => $tileIndex,
                    'is_mine' => true,
                    'multiplier' => 0.00,
                    'profit' => 0.00,
                    'clicked_at' => now(),
                ]);

                // Game Over - Lost
                $bet->update([
                    'status' => 'lost',
                    'win_amount' => 0.00,
                    'multiplier' => 0.00,
                    'bet_details' => array_merge($details, [
                        'hit_mine_index' => $tileIndex,
                        'ended_at' => now()->toIso8601String(),
                    ]),
                ]);

                // Process referral commission for losing bet
                $this->commissionService->processBetCommission($bet);

                return [
                    'success' => true,
                    'status' => 'lost',
                    'is_mine' => true,
                    'is_gem' => false,
                    'tile_index' => $tileIndex,
                    'mine_positions' => $minePositions,
                    'opened_positions' => $openedPositions,
                    'message' => 'BOOM! You hit a mine.',
                ];
            }

            // Safe Gem Picked
            $openedPositions[] = $tileIndex;
            $safePicks = count($openedPositions);
            $totalSafeTiles = 25 - $minesCount;

            // Calculate multiplier dynamically
            $multiplier = $this->generatorService->calculateMultiplier($minesCount, $safePicks, 25);
            $profit = round($bet->bet_amount * $multiplier, 2);

            // Record move in game_moves table
            GameMove::create([
                'game_bet_id' => $bet->id,
                'tile_index' => $tileIndex,
                'is_mine' => false,
                'multiplier' => $multiplier,
                'profit' => $profit,
                'clicked_at' => now(),
            ]);

            $autoWon = ($safePicks >= $totalSafeTiles);

            if ($autoWon) {
                // Auto cashout if all safe tiles cleared
                $bet->update([
                    'status' => 'won',
                    'win_amount' => $profit,
                    'multiplier' => $multiplier,
                    'cashout_multiplier' => $multiplier,
                    'bet_details' => array_merge($details, [
                        'opened_positions' => $openedPositions,
                        'current_multiplier' => $multiplier,
                        'current_profit' => $profit,
                        'ended_at' => now()->toIso8601String(),
                    ]),
                ]);

                $this->walletService->credit(
                    $user->id,
                    $profit,
                    'main',
                    'win',
                    "MINES_AUTO_WIN_{$bet->id}",
                    "Mines Cleared All Tiles! Won Rs {$profit}"
                );

                $this->commissionService->processBetCommission($bet);

                $freshBalance = number_format($user->wallet->fresh()->main_balance, 2);

                return [
                    'success' => true,
                    'status' => 'won',
                    'is_mine' => false,
                    'is_gem' => true,
                    'tile_index' => $tileIndex,
                    'multiplier' => number_format($multiplier, 2),
                    'current_profit' => number_format($profit, 2),
                    'win_amount' => number_format($profit, 2),
                    'new_balance' => $freshBalance,
                    'mine_positions' => $minePositions,
                    'message' => 'CONGRATULATIONS! You cleared all safe tiles!',
                ];
            }

            // Update bet progress
            $bet->update([
                'multiplier' => $multiplier,
                'win_amount' => $profit,
                'bet_details' => array_merge($details, [
                    'opened_positions' => $openedPositions,
                    'current_multiplier' => $multiplier,
                    'current_profit' => $profit,
                ]),
            ]);

            return [
                'success' => true,
                'status' => 'active',
                'is_mine' => false,
                'is_gem' => true,
                'tile_index' => $tileIndex,
                'multiplier' => number_format($multiplier, 2),
                'current_profit' => number_format($profit, 2),
                'safe_picks' => $safePicks,
            ];
        });
    }

    /**
     * Cash out active Mines game
     */
    public function cashoutGame(User $user, int $betId): array
    {
        return DB::transaction(function () use ($user, $betId) {
            $bet = GameBet::where('user_id', $user->id)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->findOrFail($betId);

            $details = $bet->bet_details ?? [];
            $minePositions = $details['mine_positions'] ?? [];
            $openedPositions = $details['opened_positions'] ?? [];

            if (empty($openedPositions)) {
                throw new \Exception("Must open at least one safe tile before cashing out.");
            }

            $currentMultiplier = (float)($bet->multiplier ?? 1.00);

            if ($currentMultiplier < 1.00) {
                $currentMultiplier = 1.00;
            }

            $winAmount = round($bet->bet_amount * $currentMultiplier, 2);

            $bet->update([
                'win_amount' => $winAmount,
                'cashout_multiplier' => $currentMultiplier,
                'status' => 'won',
                'bet_details' => array_merge($details, [
                    'cashed_out' => true,
                    'ended_at' => now()->toIso8601String(),
                ]),
            ]);

            // Credit user wallet
            $this->walletService->credit(
                $user->id,
                $winAmount,
                'main',
                'win',
                "MINES_CASHOUT_{$bet->id}",
                "Cashed out Mines at {$currentMultiplier}x multiplier"
            );

            $this->commissionService->processBetCommission($bet);

            $freshBalance = number_format($user->wallet->fresh()->main_balance, 2);

            return [
                'success' => true,
                'status' => 'won',
                'win_amount' => number_format($winAmount, 2),
                'multiplier' => number_format($currentMultiplier, 2),
                'new_balance' => $freshBalance,
                'mine_positions' => $minePositions,
                'message' => "Successfully cashed out Rs {$winAmount}!",
            ];
        });
    }
}
