<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Events\CounterUpdated;
use Illuminate\Support\Facades\Log;

class TestController extends Controller
{
    /**
     * Increment counter and broadcast
     */
    public function incrementCounter(Request $request)
    {
        $counter = $request->input('counter', 0);

        Log::info('📊 Counter API called', ['counter' => $counter]);

        try {
            // Broadcast immediately
            Log::info('📡 Broadcasting CounterUpdated event to test-counter channel');
            broadcast(new CounterUpdated($counter));
            Log::info('✅ Broadcast completed successfully');

            return response()->json([
                'success' => true,
                'message' => 'Counter updated and broadcast sent',
                'counter' => $counter
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Broadcast failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Broadcast failed: ' . $e->getMessage(),
                'counter' => $counter
            ], 500);
        }
    }
}
