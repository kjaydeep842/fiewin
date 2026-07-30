<?php

namespace App\Enums;

enum GameType: string
{
    case FAST_PARITY = 'fast_parity';
    case PARITY = 'parity';
    case MINES = 'mines';
    case CRASH = 'crash';
    case JET = 'jet';
    case DICE = 'dice';
    case SPIN_WHEEL = 'spin_wheel';
    case LUCKY_NUMBER = 'lucky_number';
    case LOTTERY = 'lottery';
    case CARD_GAME = 'card_game';
    case LUDO = 'ludo';
    case HTML5 = 'html5';
}
