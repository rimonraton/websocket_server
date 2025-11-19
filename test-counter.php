<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Events\CounterUpdated;

echo "Testing CounterUpdated broadcast...\n\n";

for ($i = 1; $i <= 5; $i++) {
    echo "Broadcasting counter: $i\n";
    broadcast(new CounterUpdated($i));
    sleep(1);
}

echo "\n✅ Test complete! Check driver dashboard for counter updates.\n";
