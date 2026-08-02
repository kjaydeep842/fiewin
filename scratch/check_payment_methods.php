<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PaymentMethod;

$methods = PaymentMethod::all();
foreach ($methods as $m) {
    echo "ID: {$m->id} | Code: '{$m->code}' | Name: '{$m->name}' | Type: '{$m->type}' | Bonus: {$m->bonus_percentage}% | Active: {$m->is_active}\n";
}
