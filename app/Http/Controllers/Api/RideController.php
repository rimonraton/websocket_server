<?php

namespace App\Http\Controllers\Api;

use App\Events\RidePositionUpdated;
use App\Events\RideRequest;
use App\Events\DriverAccepted;
use App\Events\DriverPickedUp;
use App\Events\RideCompleted;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RideController extends Controller
{
    /**
     * Update rider position in real-time
     */
    public function updatePosition(Request $request, $rideId)
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'bearing' => 'nullable|numeric',
            'speed' => 'nullable|numeric',
            'driverId' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        // Broadcast the position update
        broadcast(new RidePositionUpdated(
            $rideId,
            $data['lat'],
            $data['lng'],
            $data['bearing'] ?? null,
            $data['speed'] ?? null,
            $data['driverId'] ?? null
        ));

        return response()->json([
            'success' => true,
            'message' => 'Position updated successfully',
            'data' => [
                'rideId' => $rideId,
                'lat' => $data['lat'],
                'lng' => $data['lng'],
            ]
        ]);
    }

    /**
     * Create a new ride
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'passengerId' => 'required|integer',
            'passengerName' => 'required|string',
            'passengerPhone' => 'nullable|string',
            'pickup' => 'required|string',
            'pickupLat' => 'nullable|numeric',
            'pickupLng' => 'nullable|numeric',
            'destination' => 'required|string',
            'destinationLat' => 'nullable|numeric',
            'destinationLng' => 'nullable|numeric',
            'vehicleType' => 'required|string|in:sedan,suv,bike',
            'price' => 'required|numeric',
            'distance' => 'required|numeric',
            'duration' => 'required|numeric',
            'priceBreakdown' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $rideId = 'ride-' . uniqid();

        $rideData = [
            'rideId' => $rideId,
            'pickup' => $request->pickup,
            'destination' => $request->destination,
            'vehicleType' => $request->vehicleType,
            'price' => $request->price,
            'distance' => $request->distance,
            'duration' => $request->duration,
            'status' => 'pending',
            'passengerName' => $request->passengerName,
            'passengerContact' => $request->passengerPhone,
        ];

        // Save to dms-backend database
        try {
            $response = Http::post(config('app.dms_backend_url') . '/api/ride-bookings', [
                'ride_id' => $rideId,
                'passenger_id' => $request->passengerId,
                'passenger_name' => $request->passengerName,
                'passenger_phone' => $request->passengerPhone,
                'vehicle_type' => $request->vehicleType,
                'pickup_location' => $request->pickup,
                'pickup_lat' => $request->pickupLat ? (string)$request->pickupLat : null,
                'pickup_lng' => $request->pickupLng ? (string)$request->pickupLng : null,
                'destination_location' => $request->destination,
                'destination_lat' => $request->destinationLat ? (string)$request->destinationLat : null,
                'destination_lng' => $request->destinationLng ? (string)$request->destinationLng : null,
                'distance' => $request->distance,
                'duration' => $request->duration,
                'price' => $request->price,
                'price_breakdown' => $request->priceBreakdown,
            ]);

            Log::info('Ride saved to database', ['response' => $response->body()]);

            if (!$response->successful()) {
                Log::error('Failed to save ride to database', ['response' => $response->body()]);
            }
        } catch (\Exception $e) {
            Log::error('Exception saving ride to database', ['error' => $e->getMessage()]);
        }

        // Broadcast ride request to all online drivers
        broadcast(new RideRequest($rideData));

        return response()->json([
            'success' => true,
            'message' => 'Ride created successfully',
            'data' => $rideData
        ], 201);
    }

    /**
     * Get ride details
     */
    public function show($rideId)
    {
        // In a real app, fetch from database
        return response()->json([
            'success' => true,
            'data' => [
                'rideId' => $rideId,
                'status' => 'active',
                'driver' => [
                    'name' => 'Demo Driver',
                    'vehicle' => 'Sedan',
                    'rating' => 4.8
                ]
            ]
        ]);
    }

    /**
     * Driver accepts a ride
     */
    public function accept(Request $request, $rideId)
    {
        $validator = Validator::make($request->all(), [
            'driverId' => 'required',
            'driverName' => 'required|string',
            'driverPhone' => 'nullable|string',
            'driverRating' => 'nullable|numeric',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $driverData = [
            'id' => $request->driverId,
            'name' => $request->driverName,
            'phone' => $request->driverPhone,
            'rating' => $request->driverRating ?? 4.8,
            'lat' => $request->lat,
            'lng' => $request->lng,
        ];

        // Update database
        try {
            $response = Http::post(config('app.dms_backend_url') . '/api/ride-bookings/' . $rideId . '/accept', [
                'driver_id' => $request->driverId,
                'driver_name' => $request->driverName,
                'driver_phone' => $request->driverPhone,
            ]);

            Log::info('Ride acceptance saved to database', [
                'ride_id' => $rideId,
                'driver_id' => $request->driverId,
                'response' => $response->body()
            ]);

            if (!$response->successful()) {
                Log::error('Failed to update ride acceptance in database', ['response' => $response->body()]);
            }
        } catch (\Exception $e) {
            Log::error('Exception updating ride acceptance in database', ['error' => $e->getMessage()]);
        }

        // Broadcast driver acceptance to the rider
        broadcast(new DriverAccepted($rideId, $driverData));

        return response()->json([
            'success' => true,
            'message' => 'Ride accepted successfully',
            'data' => [
                'rideId' => $rideId,
                'driver' => $driverData,
                'status' => 'accepted'
            ]
        ]);
    }

    /**
     * Driver picks up passenger
     */
    public function pickup(Request $request, $rideId)
    {
        $validator = Validator::make($request->all(), [
            'driverId' => 'required',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = [
            'rideId' => $rideId,
            'driverId' => $request->driverId,
            'lat' => $request->lat,
            'lng' => $request->lng,
            'pickedUpAt' => now()->toISOString(),
        ];

        // Update database
        try {
            $response = Http::post(config('app.dms_backend_url') . '/api/ride-bookings/' . $rideId . '/pickup');

            Log::info('Pickup status saved to database', [
                'ride_id' => $rideId,
                'response' => $response->body()
            ]);

            if (!$response->successful()) {
                Log::error('Failed to update pickup in database', ['response' => $response->body()]);
            }
        } catch (\Exception $e) {
            Log::error('Exception updating pickup in database', ['error' => $e->getMessage()]);
        }

        // Broadcast pickup event to the rider
        broadcast(new DriverPickedUp($rideId, $data));

        return response()->json([
            'success' => true,
            'message' => 'Passenger picked up successfully',
            'data' => $data
        ]);
    }

    /**
     * Complete a ride
     */
    public function complete(Request $request, $rideId)
    {
        $validator = Validator::make($request->all(), [
            'driverId' => 'required',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = [
            'rideId' => $rideId,
            'driverId' => $request->driverId,
            'completedAt' => now()->toISOString(),
        ];

        // Update database
        try {
            $response = Http::post(config('app.dms_backend_url') . '/api/ride-bookings/' . $rideId . '/complete');

            Log::info('Ride completion saved to database', [
                'ride_id' => $rideId,
                'response' => $response->body()
            ]);

            if (!$response->successful()) {
                Log::error('Failed to update ride completion in database', ['response' => $response->body()]);
            }
        } catch (\Exception $e) {
            Log::error('Exception updating ride completion in database', ['error' => $e->getMessage()]);
        }

        // Broadcast completion event
        broadcast(new RideCompleted($rideId, $data));

        return response()->json([
            'success' => true,
            'message' => 'Ride completed successfully',
            'data' => $data
        ]);
    }
}
