<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Admin\AdminFastParityController;
use App\Http\Controllers\Admin\AdminParityController;
use App\Http\Controllers\Admin\AdminMinesController;
use App\Http\Controllers\Admin\AdminSpinWheelController;
use App\Http\Controllers\Admin\AdminDiceController;

$controllers = [
    'Fast Parity' => AdminFastParityController::class,
    'Parity 3-Min' => AdminParityController::class,
    'Mines' => AdminMinesController::class,
    'Spin Wheel' => AdminSpinWheelController::class,
    'Dice Roll' => AdminDiceController::class,
];

foreach ($controllers as $name => $class) {
    $instance = app($class);
    echo "✓ Loaded {$name} Admin Controller: {$class}\n";
}

echo "\nAll game-wise admin controllers loaded successfully!\n";
