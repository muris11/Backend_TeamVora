<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Mail;

Mail::raw('Halo dari TeamVora! Ini email test SMTP.', function ($message) {
    $message->to('admin@teamvora.web.id')
            ->subject('Test SMTP - TeamVora');
});

echo "Email sent successfully!\n";
