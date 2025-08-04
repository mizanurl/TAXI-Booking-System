<?php

namespace App\Services;

use App\Models\Airport; // Assuming Airport model is used
use App\Models\ExtraCharge; // Assuming ExtraCharge model is used
use App\Services\GoogleMapsService;
use App\Services\SlabService; // Assuming you have this for base fare slabs
use App\Services\CarService; // Assuming you have this for car-specific pricing
use App\Services\CommonSettingService; // For general settings like stopover cost
use App\Services\ExtraChargeService; // For extra charges based on zip codes
use App\Exceptions\NotFoundException; // For airport not found

class FareCalculatorService
{
    private GoogleMapsService $googleMapsService;
    private SlabService $slabService;
    private CarService $carService; // Used to get airport details if needed
    private CommonSettingService $commonSettingService;
    private ExtraChargeService $extraChargeService;
    private AirportService $airportService; // NEW: Added AirportService to get airport details directly

    public function __construct(
        GoogleMapsService $googleMapsService,
        SlabService $slabService,
        CarService $carService,
        CommonSettingService $commonSettingService,
        ExtraChargeService $extraChargeService,
        AirportService $airportService
    ) {
        $this->googleMapsService = $googleMapsService;
        $this->slabService = $slabService;
        $this->carService = $carService;
        $this->commonSettingService = $commonSettingService;
        $this->extraChargeService = $extraChargeService;
        $this->airportService = $airportService;
    }

    /**
     * Calculates the total fare based on various parameters.
     *
     * @param array $data Contains all necessary booking details:
     * - 'service_type': 'from_airport', 'to_airport', 'door_to_door'
     * - 'pickup_lat', 'pickup_lng', 'pickup_zip_code' (for non-airport pickups)
     * - 'dropoff_lat', 'dropoff_lng', 'dropoff_zip_code' (for non-airport dropoffs)
     * - 'pickup_airport_id' (if 'from_airport' service)
     * - 'dropoff_airport_id' (if 'to_airport' service)
     * - 'adults', 'children', 'infant_seats', 'toddler_seats', 'booster_seats', 'stopovers'
     * - 'car_id' (optional, if a specific car is chosen)
     * @return array The calculated fare breakdown.
     * @throws \Exception If essential data is missing or calculation fails.
     */
    public function calculateFare(array $data): array
    {
        $baseFare = 0.0;
        $distance = 0.0; // in meters
        $duration = 0; // in seconds
        $extraChargesTotal = 0.0;
        $extraTollChargesTotal = 0.0;
        $airportToll = 0.0;
        $airportParkingSurcharge = 0.0;
        $childSeatCost = 0.0;
        $stopoverCost = 0.0;

        // 1. Determine origin and destination coordinates and zip codes
        $originLat = $data['pickup_lat'] ?? null;
        $originLng = $data['pickup_lng'] ?? null;
        $originZipCode = $data['pickup_zip_code'] ?? null;

        $destinationLat = $data['dropoff_lat'] ?? null;
        $destinationLng = $data['dropoff_lng'] ?? null;
        $destinationZipCode = $data['dropoff_zip_code'] ?? null;

        $serviceType = $data['service_type'];

        // Handle airport specific logic for coordinates and tolls/surcharges
        if ($serviceType === 'from_airport' && isset($data['pickup_airport_id'])) {
            $airport = $this->airportService->getAirportById((int)$data['pickup_airport_id']);
            if (!$airport) {
                throw new NotFoundException("Pickup airport with ID {$data['pickup_airport_id']} not found.");
            }
            $originLat = $airport->latitude;
            $originLng = $airport->longitude;
            // Assuming your Airport model has a zipCode property
            // If not, you'll need to add it or fetch it via another service
            $originZipCode = $airport->zipCode ?? null;
            $airportToll += $airport->fromTaxToll;
        } elseif ($serviceType === 'to_airport' && isset($data['dropoff_airport_id'])) {
            $airport = $this->airportService->getAirportById((int)$data['dropoff_airport_id']);
            if (!$airport) {
                throw new NotFoundException("Dropoff airport with ID {$data['dropoff_airport_id']} not found.");
            }
            $destinationLat = $airport->latitude;
            $destinationLng = $airport->longitude;
            // Assuming your Airport model has a zipCode property
            // If not, you'll need to add it or fetch it via another service
            $destinationZipCode = $airport->zipCode ?? null;
            $airportToll += $airport->toTaxToll;
        }

        // Validate that we have coordinates for distance calculation
        if (empty($originLat) || empty($originLng) || empty($destinationLat) || empty($destinationLng)) {
            throw new \Exception("Missing complete origin or destination coordinates for fare calculation.");
        }

        // 2. Get Distance and Duration from Google Maps Service
        try {
            $distanceMatrix = $this->googleMapsService->getDistanceMatrix(
                (float)$originLat, (float)$originLng, (float)$destinationLat, (float)$destinationLng
            );
            $distance = $distanceMatrix['distance']; // in meters
            $duration = $distanceMatrix['duration']; // in seconds
        } catch (\Exception $e) {
            error_log("Error getting distance matrix: " . $e->getMessage());
            // Fallback or throw a more specific error
            throw new \Exception("Could not calculate distance and duration for fare. " . $e->getMessage());
        }

        // 3. Calculate Base Fare using Slab Service (Placeholder)
        // This is where your actual slab logic would go. For now, a simple distance-based calculation.
        // You would typically use $this->slabService->calculateBaseFare($distance, $duration, $data['car_id'] ?? null);
        $baseFare = ($distance / 1000) * 2.5; // Example: $2.5 per km (distance is in meters)

        // 4. Calculate Child Seat Costs
        $infantSeats = (int) ($data['infant_seats'] ?? 0);
        $toddlerSeats = (int) ($data['toddler_seats'] ?? 0);
        $boosterSeats = (int) ($data['booster_seats'] ?? 0);

        // Assuming costs are fixed as per frontend example comments
        $childSeatCost = ($infantSeats * 10) + ($toddlerSeats * 10) + ($boosterSeats * 5);

        // 5. Calculate Stopover Cost
        $stopovers = (int) ($data['stopovers'] ?? 0);
        $stopoverCost = $stopovers * 10; // Assuming $10 per stop

        // 6. Calculate Extra Charges based on Zip Codes
        $zipCodesToCheck = [];
        if (!empty($originZipCode)) {
            $zipCodesToCheck[] = $originZipCode;
        }
        if (!empty($destinationZipCode)) {
            $zipCodesToCheck[] = $destinationZipCode;
        }

        // Use array_unique to avoid processing the same zip code multiple times if it's both origin and destination
        $applicableExtraCharges = $this->extraChargeService->getApplicableExtraCharges(array_unique($zipCodesToCheck));

        foreach ($applicableExtraCharges as $charge) {
            $extraChargesTotal += $charge->extraCharge;
            $extraTollChargesTotal += $charge->extraTollCharge;
        }

        // 7. Calculate Tunnel Charges (Placeholder)
        // This would involve checking if the route passes through a tunnel with charges.
        // $tunnelCharge = $this->tunnelChargeService->getApplicableTunnelCharge($originLat, $originLng, $destinationLat, $destinationLng);

        // Total Fare Calculation
        $totalFare = $baseFare + $childSeatCost + $stopoverCost + $extraChargesTotal + $extraTollChargesTotal + $airportToll + $airportParkingSurcharge;

        return [
            'base_fare' => round($baseFare, 2),
            'distance_km' => round($distance / 1000, 2), // Convert meters to km for output
            'duration_minutes' => round($duration / 60, 0), // Convert seconds to minutes for output
            'child_seat_cost' => round($childSeatCost, 2),
            'stopover_cost' => round($stopoverCost, 2),
            'extra_charges_total' => round($extraChargesTotal, 2),
            'extra_toll_charges_total' => round($extraTollChargesTotal, 2),
            'airport_toll' => round($airportToll, 2),
            'airport_parking_surcharge' => round($airportParkingSurcharge, 2),
            'total_fare' => round($totalFare, 2),
            'details' => [ // Optional: for debugging or detailed display
                'origin_zip' => $originZipCode,
                'destination_zip' => $destinationZipCode,
                'applicable_extra_charges_areas' => array_map(fn($ec) => $ec->areaName, $applicableExtraCharges)
            ]
        ];
    }
}