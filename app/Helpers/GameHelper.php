<?php

namespace App\Helpers;

class GameHelper
{
    /**
     * Determine number and colors for Fast Parity
     * Number 0: Red + Violet
     * Number 5: Green + Violet
     * Odd 1,3,7,9: Green
     * Even 2,4,6,8: Red
     */
    public static function getParityColorResult(int $number): array
    {
        $colors = [];
        if ($number === 0) {
            $colors = ['red', 'violet'];
        } elseif ($number === 5) {
            $colors = ['green', 'violet'];
        } elseif ($number % 2 === 1) {
            $colors = ['green'];
        } else {
            $colors = ['red'];
        }

        return [
            'number' => $number,
            'colors' => $colors,
            'primary_color' => $colors[0],
            'has_violet' => in_array('violet', $colors),
        ];
    }

    /**
     * Generate Period Number based on timestamp & interval (30s or 60s)
     */
    public static function generatePeriodNumber(string $gameCode = 'fast_parity', int $secondsInterval = 30): string
    {
        $timestamp = time();
        $periodIndex = (int)floor($timestamp / $secondsInterval);
        $date = date('Ymd', $timestamp);

        return $date . str_pad($periodIndex % 10000, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate provably fair crash multiplier
     */
    public static function generateCrashPoint(string $seed, string $salt = 'rivexa_crash_salt'): float
    {
        $hash = hash_hmac('sha256', $seed, $salt);
        $subHash = substr($hash, 0, 8);
        $hexInt = hexdec($subHash);
        
        if ($hexInt % 33 === 0) {
            return 1.00; // House edge instantly crashes at 1.00x
        }
        
        $e = pow(2, 32);
        $multiplier = floor((100 * $e - $hexInt) / ($e - $hexInt)) / 100;
        
        return max(1.01, min($multiplier, 500.00));
    }
}
