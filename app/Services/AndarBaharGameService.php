<?php

namespace App\Services;

use App\Models\AndarBaharBet;
use App\Models\AndarBaharResult;
use App\Models\AndarBaharRound;
use App\Models\AndarBaharSetting;
use App\Models\Game;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AndarBaharGameService
{
    protected WalletService $walletService;
    protected ReferralCommissionService $commissionService;

    // 52 Card Deck definitions
    protected const SUITS = ['♠', '♥', '♦', '♣'];
    protected const RANKS = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];

    public function __construct(WalletService $walletService, ReferralCommissionService $commissionService)
    {
        $this->walletService = $walletService;
        $this->commissionService = $commissionService;
    }

    /**
     * Generate sequential period number resetting hourly.
     * Format: YYYYMMDDHH0001, YYYYMMDDHH0002...
     */
    public function generateSequentialPeriodNumber(): string
    {
        $hourPrefix = date('YmdH');

        $latest = AndarBaharRound::where('period_number', 'LIKE', "{$hourPrefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($latest) {
            $seqStr = substr($latest->period_number, strlen($hourPrefix));
            $seq = (int)$seqStr + 1;
        } else {
            $seq = 1;
        }

        return $hourPrefix . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get or create current active period round
     */
    public function getCurrentRound(): AndarBaharRound
    {
        $settings = AndarBaharSetting::getSettings();
        $roundSeconds = $settings->round_seconds > 0 ? $settings->round_seconds : 60;

        $active = AndarBaharRound::where('status', 'betting')
            ->orderBy('id', 'desc')
            ->first();

        if ($active) {
            $startedAt = $active->started_at ? $active->started_at->timestamp : time();
            $elapsed = max(0, time() - $startedAt);
            if ($elapsed < $roundSeconds) {
                return $active;
            }
        }

        // Create new sequential round
        $periodNumber = $this->generateSequentialPeriodNumber();
        return AndarBaharRound::create([
            'period_number' => $periodNumber,
            'open_card' => $this->getRandomCard(),
            'status' => 'betting',
            'started_at' => now(),
        ]);
    }

    /**
     * Generate full 52-card deck
     */
    public function getFullDeck(): array
    {
        $deck = [];
        foreach (self::SUITS as $suit) {
            foreach (self::RANKS as $rank) {
                $deck[] = $rank . $suit;
            }
        }
        return $deck;
    }

    /**
     * Get single random card
     */
    public function getRandomCard(): string
    {
        $deck = $this->getFullDeck();
        return $deck[array_rand($deck)];
    }

    /**
     * Extract rank from a card string (e.g. "10♠" -> "10", "A♥" -> "A")
     */
    public static function getCardRank(string $card): string
    {
        return mb_substr($card, 0, -1);
    }

    /**
     * Settle an Andar Bahar Round
     */
    public function settleRound(AndarBaharRound $round): AndarBaharResult
    {
        return DB::transaction(function () use ($round) {
            // Check if already settled
            $existingResult = AndarBaharResult::where('period_number', $round->period_number)->first();
            if ($existingResult) {
                return $existingResult;
            }

            $settings = AndarBaharSetting::getSettings();
            $pendingBets = AndarBaharBet::where('period_number', $round->period_number)
                ->where('status', 'pending')
                ->get();

            // Determine Winning Side (Manual Override or Fair Random Probability)
            $winner = 'andar';
            $isOverride = false;

            if (!empty($settings->manual_override_winner) && in_array($settings->manual_override_winner, ['andar', 'bahar', 'tie'])) {
                $winner = $settings->manual_override_winner;
                $isOverride = true;
                // Reset override after settlement
                $settings->update(['manual_override_winner' => null]);
            } else {
                // Fair random outcome: Andar ~48%, Bahar ~48%, Tie ~4%
                $rand = rand(1, 100);
                if ($rand <= 48) {
                    $winner = 'andar';
                } elseif ($rand <= 96) {
                    $winner = 'bahar';
                } else {
                    $winner = 'tie';
                }
            }

            // Generate deal sequence matching winner
            $dealData = $this->generateDealSequence($round->open_card, $winner);

            // Update round
            $round->update([
                'winner' => $winner,
                'deal_sequence' => $dealData['sequence'],
                'matching_card' => $dealData['matching_card'],
                'deal_count' => count($dealData['sequence']),
                'status' => 'settled',
                'manual_override' => $isOverride,
                'settled_at' => now(),
            ]);

            // Create result entry
            $seed = Str::random(16);
            $hash = hash('sha256', $round->period_number . '-' . $winner . '-' . $seed);

            $gameResult = AndarBaharResult::create([
                'period_number' => $round->period_number,
                'open_card' => $round->open_card,
                'winner' => $winner,
                'deal_count' => count($dealData['sequence']),
                'winning_card' => $dealData['matching_card'],
                'result_data' => [
                    'open_card' => $round->open_card,
                    'winner' => $winner,
                    'matching_card' => $dealData['matching_card'],
                    'deal_sequence' => $dealData['sequence'],
                    'deal_count' => count($dealData['sequence']),
                ],
                'provably_fair_hash' => $hash,
                'seed' => $seed,
                'manual_override' => $isOverride,
                'settled_at' => now(),
            ]);

            // Settle pending bets & pay winners
            foreach ($pendingBets as $bet) {
                $odds = match ($bet->bet_option) {
                    'andar' => $settings->andar_odds,
                    'bahar' => $settings->bahar_odds,
                    'tie' => $settings->tie_odds,
                    default => 2.00,
                };

                if ($bet->bet_option === $winner) {
                    $winAmount = round($bet->bet_amount * $odds, 2);
                    $bet->update([
                        'andar_bahar_round_id' => $round->id,
                        'win_amount' => $winAmount,
                        'multiplier' => $odds,
                        'status' => 'won',
                    ]);

                    // Credit winner's wallet
                    $this->walletService->credit(
                        $bet->user_id,
                        $winAmount,
                        'main',
                        'win',
                        "ANDAR_BAHAR_WIN_{$bet->id}",
                        "Won Rs {$winAmount} on Andar Bahar Period #{$round->period_number}"
                    );
                } else {
                    $bet->update([
                        'andar_bahar_round_id' => $round->id,
                        'win_amount' => 0.00,
                        'multiplier' => 0.00,
                        'status' => 'lost',
                    ]);
                }

                // Process multi-tier referral commissions for this bet
                $this->commissionService->processBetCommission($bet);
            }

            return $gameResult;
        });
    }

    /**
     * Calculate potential house payout for a prospective winning outcome
     */
    protected function calculateTotalPayoutForOutcome(string $outcome, $pendingBets, AndarBaharSetting $settings): float
    {
        $total = 0.0;
        foreach ($pendingBets as $bet) {
            if ($bet->bet_option === $outcome) {
                $odds = match ($outcome) {
                    'andar' => $settings->andar_odds,
                    'bahar' => $settings->bahar_odds,
                    'tie' => $settings->tie_odds,
                    default => 2.00,
                };
                $total += ($bet->bet_amount * $odds);
            }
        }
        return $total;
    }

    /**
     * Generate deal sequence for Andar Bahar
     */
    public function generateDealSequence(string $openCard, string $targetWinner): array
    {
        $openRank = self::getCardRank($openCard);
        $fullDeck = $this->getFullDeck();

        // Remove open card from deck
        $deck = array_values(array_filter($fullDeck, fn($c) => $c !== $openCard));

        // Separate matching rank cards and non-matching rank cards
        $matchingCards = array_values(array_filter($deck, fn($c) => self::getCardRank($c) === $openRank));
        $nonMatchingCards = array_values(array_filter($deck, fn($c) => self::getCardRank($c) !== $openRank));

        shuffle($nonMatchingCards);
        shuffle($matchingCards);

        $sequence = [];
        $side = 'andar'; // Deals start with Andar
        $matchingCard = $matchingCards[0];

        if ($targetWinner === 'tie') {
            // Special Tie dealing scenario: matching card dealt or specific tie rule
            // In classic Andar Bahar tie, matching card rank appears on first deal for both sides or simultaneous match
            // Here, deal 1 card non-matching to Andar, 1 matching to Bahar, or vice versa as configured
            $sequence[] = ['card' => $nonMatchingCards[0], 'side' => 'andar'];
            $sequence[] = ['card' => $matchingCard, 'side' => 'bahar'];
        } else {
            // Determine maximum non-matching cards to deal before matching card
            // If targetWinner is 'andar', matching card must land on an odd-numbered step (1, 3, 5...)
            // If targetWinner is 'bahar', matching card must land on an even-numbered step (2, 4, 6...)
            $numNonMatching = rand(1, 15);
            if ($targetWinner === 'andar' && ($numNonMatching % 2 === 1)) {
                $numNonMatching += 1;
            } elseif ($targetWinner === 'bahar' && ($numNonMatching % 2 === 0)) {
                $numNonMatching += 1;
            }

            for ($i = 0; $i < $numNonMatching; $i++) {
                if (isset($nonMatchingCards[$i])) {
                    $sequence[] = [
                        'card' => $nonMatchingCards[$i],
                        'side' => $side,
                    ];
                    $side = ($side === 'andar') ? 'bahar' : 'andar';
                }
            }

            // Finally deal the matching card to the target side
            $sequence[] = [
                'card' => $matchingCard,
                'side' => $side,
            ];
        }

        return [
            'sequence' => $sequence,
            'matching_card' => $matchingCard,
        ];
    }
}
