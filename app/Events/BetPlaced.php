<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BetPlaced implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $gameCode;
    public array $betData;

    public function __construct(string $gameCode, array $betData)
    {
        $this->gameCode = $gameCode;
        $this->betData = $betData;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('game.' . strtolower($this->gameCode)),
        ];
    }

    public function broadcastAs(): string
    {
        return 'BetPlaced';
    }
}
