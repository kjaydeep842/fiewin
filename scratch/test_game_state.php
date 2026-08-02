<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Http\Controllers\Player\GameController;
use Illuminate\Http\Request;

try {
    $user = User::first();
    auth()->login($user);

    $req = Request::create('/games/fast_parity/state?interval=30', 'GET');
    $req->headers->set('Accept', 'application/json');
    $req->headers->set('X-Requested-With', 'XMLHttpRequest');

    $controller = app(GameController::class);
    $res = $controller->getGameState('fast_parity', $req);

    echo "Status: " . $res->getStatusCode() . "\n";
    echo "Content: " . $res->getContent() . "\n";

} catch (\Throwable $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}
