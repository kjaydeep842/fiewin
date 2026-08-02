<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameStateUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $gameCode;
    public array $state;

    public function __construct(string $gameCode, array $state)
    {
        $this->gameCode = $gameCode;
        $this->state = $state;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('game.' . strtolower($this->gameCode)),
        ];
    }

    public function broadcastAs(): string
    {
        return 'GameStateUpdated';
    }
}
