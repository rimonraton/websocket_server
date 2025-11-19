<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing broadcast to Reverb...\n\n";

// Get config
$config = config('broadcasting.connections.reverb');
echo "Config:\n";
echo "  Host: " . ($config['options']['host'] ?? 'not set') . "\n";
echo "  Port: " . ($config['options']['port'] ?? 'not set') . "\n";
echo "  Scheme: " . ($config['options']['scheme'] ?? 'not set') . "\n";
echo "  App ID: " . ($config['app_id'] ?? 'not set') . "\n";
echo "  Key: " . ($config['key'] ?? 'not set') . "\n\n";

try {
    echo "Broadcasting CounterUpdated event with value 777...\n";
    broadcast(new \App\Events\CounterUpdated(777));
    echo "✅ Broadcast function executed successfully!\n\n";
    
    echo "Sleeping 2 seconds to allow async processing...\n";
    sleep(2);
    
    echo "If you see a broadcast message in Reverb terminal, it worked!\n";
    echo "If not, check:\n";
    echo "1. Is Reverb running?\n";
    echo "2. Can Laravel connect to localhost:8085?\n";
    echo "3. Are credentials correct?\n";
    
} catch (\Exception $e) {
    echo "❌ Broadcast failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
