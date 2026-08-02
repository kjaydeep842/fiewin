<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Http\Controllers\Admin\AdminCrashController;
use App\Http\Controllers\Admin\AdminJetController;
use Illuminate\Http\Request;

try {
    $user = User::where('role', 'admin')->first() ?? User::first();
    auth()->login($user);

    echo "Testing Admin Crash Reports...\n";
    $crashCtrl = app(AdminCrashController::class);
    $req = Request::create('/admin/crash-admin/reports', 'GET');
    $resCrash = $crashCtrl->reports($req);
    $html1 = $resCrash->render();
    echo "Crash Reports Rendered Successfully (" . strlen($html1) . " bytes)\n";

    echo "Testing Admin Jet Reports...\n";
    $jetCtrl = app(AdminJetController::class);
    $reqJet = Request::create('/admin/jet-admin/reports', 'GET');
    $resJet = $jetCtrl->reports($reqJet);
    $html2 = $resJet->render();
    echo "Jet Reports Rendered Successfully (" . strlen($html2) . " bytes)\n";

} catch (\Throwable $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
