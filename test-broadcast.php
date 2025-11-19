<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Events\RideRequest;

echo "Testing RideRequest broadcast...\n";

$rideData = [
    'rideId' => 'test-ride-' . time(),
    'pickup' => 'Test Pickup Location',
    'destination' => 'Test Destination',
    'vehicleType' => 'sedan',
    'price' => 100,
    'distance' => 5.5,
    'duration' => 15,
    'status' => 'pending'
];

echo "Broadcasting ride: " . json_encode($rideData, JSON_PRETTY_PRINT) . "\n";

try {
    broadcast(new RideRequest($rideData));
    echo "✅ Broadcast sent successfully!\n";
    echo "Check driver dashboard for notification.\n";
} catch (\Exception $e) {
    echo "❌ Broadcast failed: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
