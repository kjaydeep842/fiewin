<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Game;

foreach (Game::all() as $g) {
    echo "ID: {$g->id} | Code: {$g->code} | Name: {$g->name} | RTP: {$g->rtp_percentage}%\n";
}
