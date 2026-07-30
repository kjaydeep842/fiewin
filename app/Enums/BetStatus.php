<?php

namespace App\Enums;

enum BetStatus: string
{
    case PENDING = 'pending';
    case WON = 'won';
    case LOST = 'lost';
    case CANCELLED = 'cancelled';
}
