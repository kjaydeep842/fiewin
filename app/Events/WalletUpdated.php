<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WalletUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $userId;
    public string $balance;
    public string $changeType;

    public function __construct(int $userId, string $balance, string $changeType = 'update')
    {
        $this->userId = $userId;
        $this->balance = $balance;
        $this->changeType = $changeType;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('wallet.' . $this->userId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'WalletUpdated';
    }
}
