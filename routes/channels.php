<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Public chat channel
Broadcast::channel('chat', function () {
    return true;
});

// Drivers channel - PUBLIC for testing (all online drivers)
Broadcast::channel('drivers', function () {
    return true;
});

// Ride tracking channel - PUBLIC for testing (no auth required)
// Change to private channel with auth in production
Broadcast::channel('ride.{rideId}', function () {
    // Allow anyone to subscribe for testing
    // In production: return (int) $user->id === (int) $ride->user_id;
    return true;
});
