<?php

namespace App\Controllers\Api;

use App\Http\Request;
use App\Http\Response;
use App\Services\FareCalculatorService;
use App\Exceptions\ValidationException;
use App\Exceptions\NotFoundException;

use OpenApi\Attributes as OA;

#[OA\Tag(name: "Fare Calculation", description: "API for calculating booking fares.")]
class FareCalculatorController
{
    private FareCalculatorService $fareCalculatorService;
    private Request $request;
    private Response $response;

    public function __construct(FareCalculatorService $fareCalculatorService, Request $request, Response $response)
    {
        $this->fareCalculatorService = $fareCalculatorService;
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Calculate the estimated fare for a booking.
     * POST /api/v1/fare-calculation
     */
    #[OA\Post(
        path: "/fare-calculation",
        summary: "Calculate the estimated fare for a booking",
        tags: ["Fare Calculation"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "service_type", type: "string", enum: ["from_airport", "to_airport", "door_to_door"], example: "door_to_door"),
                    new OA\Property(property: "pickup_lat", type: "number", format: "float", example: 42.3601, description: "Required for door_to_door or to_airport"),
                    new OA\Property(property: "pickup_lng", type: "number", format: "float", example: -71.0589, description: "Required for door_to_door or to_airport"),
                    new OA\Property(property: "pickup_zip_code", type: "string", example: "02108", description: "Required for door_to_door or to_airport"),
                    new OA\Property(property: "dropoff_lat", type: "number", format: "float", example: 42.3736, description: "Required for door_to_door or from_airport"),
                    new OA\Property(property: "dropoff_lng", type: "number", format: "float", example: -71.1097, description: "Required for door_to_door or from_airport"),
                    new OA\Property(property: "dropoff_zip_code", type: "string", example: "02138", description: "Required for door_to_door or from_airport"),
                    new OA\Property(property: "pickup_airport_id", type: "integer", example: 1, description: "Required if service_type is 'from_airport'"),
                    new OA\Property(property: "dropoff_airport_id", type: "integer", example: 2, description: "Required if service_type is 'to_airport'"),
                    new OA\Property(property: "pickup_date", type: "string", format: "date", example: "2025-07-30"),
                    new OA\Property(property: "pickup_time", type: "string", example: "14:00"),
                    new OA\Property(property: "adults", type: "integer", example: 1),
                    new OA\Property(property: "children", type: "integer", example: 0),
                    new OA\Property(property: "infant_seats", type: "integer", example: 0),
                    new OA\Property(property: "toddler_seats", type: "integer", example: 0),
                    new OA\Property(property: "booster_seats", type: "integer", example: 0),
                    new OA\Property(property: "stopovers", type: "integer", example: 0),
                    new OA\Property(property: "car_id", type: "integer", example: 1, description: "Optional: ID of the selected car")
                ],
                required: ["service_type", "pickup_date", "pickup_time", "adults"]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Fare calculated successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Fare calculated successfully."),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "base_fare", type: "number", format: "float", example: 25.00),
                                new OA\Property(property: "distance_km", type: "number", format: "float", example: 10.5),
                                new OA\Property(property: "duration_minutes", type: "integer", example: 15),
                                new OA\Property(property: "child_seat_cost", type: "number", format: "float", example: 0.00),
                                new OA\Property(property: "stopover_cost", type: "number", format: "float", example: 0.00),
                                new OA\Property(property: "extra_charges_total", type: "number", format: "float", example: 5.00),
                                new OA\Property(property: "extra_toll_charges_total", type: "number", format: "float", example: 3.00),
                                new OA\Property(property: "airport_toll", type: "number", format: "float", example: 0.00),
                                new OA\Property(property: "airport_parking_surcharge", type: "number", format: "float", example: 0.00),
                                new OA\Property(property: "total_fare", type: "number", format: "float", example: 33.00),
                                new OA\Property(property: "details", type: "object", description: "Additional calculation details")
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Validation failed"),
                        new OA\Property(property: "errors", type: "object")
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Internal Server Error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Failed to calculate fare.")
                    ]
                )
            )
        ]
    )]
    public function calculate(): void
    {
        try {
            $data = $this->request->all();
            //var_dump($data);

            // Basic validation (you might want a dedicated FormRequest for this in a full application)
            $errors = [];
            if (!isset($data['service_type']) || !in_array($data['service_type'], ['from_airport', 'to_airport', 'door_to_door'])) {
                $errors['service_type'] = 'Invalid service type.';
            }
            if (empty($data['pickup_date'])) $errors['pickup_date'] = 'Pickup date is required.';
            if (empty($data['pickup_time'])) $errors['pickup_time'] = 'Pickup time is required.';
            if (!isset($data['adults']) || !is_numeric($data['adults']) || $data['adults'] < 1) {
                $errors['adults'] = 'At least one adult is required.';
            }

            // Location-specific validation
            if ($data['service_type'] === 'from_airport') {
                if (empty($data['pickupAirportId'])) $errors['pickupAirportId'] = 'Pickup airport is required.';
                if (empty($data['dropoffLat']) || empty($data['dropoffLng'])) $errors['dropoffLocation'] = 'Dropoff location coordinates are required.';
                if (empty($data['dropoffZipCode'])) $errors['dropoffZipCode'] = 'Dropoff zip code is required.';
            } elseif ($data['service_type'] === 'to_airport') {
                if (empty($data['pickupLat']) || empty($data['pickupLng'])) $errors['pickupLocation'] = 'Pickup location coordinates are required.';
                if (empty($data['pickupZipCode'])) $errors['pickupZipCode'] = 'Pickup zip code is required.';
                if (empty($data['dropoffAirportId'])) $errors['dropoffAirportId'] = 'Dropoff airport is required.';
            } elseif ($data['service_type'] === 'door_to_door') {
                if (empty($data['pickupLat']) || empty($data['pickupLng'])) $errors['pickupLocation'] = 'Pickup location coordinates are required.';
                if (empty($data['pickupZipCode'])) $errors['pickupZipCode'] = 'Pickup zip code is required.';
                if (empty($data['dropoffLat']) || empty($data['dropoffLng'])) $errors['dropoffLocation'] = 'Dropoff location coordinates are required.';
                if (empty($data['dropoffZipCode'])) $errors['dropoffZipCode'] = 'Dropoff zip code is required.';
            }

            if (!empty($errors)) {
                throw new ValidationException('Validation failed.', $errors);
            }

            // Map frontend names to backend names for FareCalculatorService
            $fareData = [
                'service_type' => $data['service_type'],
                'pickup_date' => $data['pickup_date'],
                'pickup_time' => $data['pickup_time'],
                'adults' => (int) $data['adults'],
                'children' => (int) ($data['children'] ?? 0),
                'infant_seats' => (int) ($data['infant_seats'] ?? 0),
                'toddler_seats' => (int) ($data['toddler_seats'] ?? 0),
                'booster_seats' => (int) ($data['booster_seats'] ?? 0),
                'stopovers' => (int) ($data['stopovers'] ?? 0),
                // car_id can be added here if you implement car selection in frontend
                // 'car_id' => (int)($data['car_id'] ?? null),
            ];

            // Add location details based on service type
            if ($data['service_type'] === 'from_airport') {
                $fareData['pickup_airport_id'] = (int) $data['pickupAirportId'];
                $fareData['dropoff_lat'] = (float) ($data['dropoffLat'] ?? 0);
                $fareData['dropoff_lng'] = (float) ($data['dropoffLng'] ?? 0);
                $fareData['dropoff_zip_code'] = $data['dropoffZipCode'] ?? null;
            } elseif ($data['service_type'] === 'to_airport') {
                $fareData['pickup_lat'] = (float) ($data['pickupLat'] ?? 0);
                $fareData['pickup_lng'] = (float) ($data['pickupLng'] ?? 0);
                $fareData['pickup_zip_code'] = $data['pickupZipCode'] ?? null;
                $fareData['dropoff_airport_id'] = (int) $data['dropoffAirportId'];
            } elseif ($data['service_type'] === 'door_to_door') {
                $fareData['pickup_lat'] = (float) ($data['pickupLat'] ?? 0);
                $fareData['pickup_lng'] = (float) ($data['pickupLng'] ?? 0);
                $fareData['pickup_zip_code'] = $data['pickupZipCode'] ?? null;
                $fareData['dropoff_lat'] = (float) ($data['dropoffLat'] ?? 0);
                $fareData['dropoff_lng'] = (float) ($data['dropoffLng'] ?? 0);
                $fareData['dropoff_zip_code'] = $data['dropoffZipCode'] ?? null;
            }


            $fareBreakdown = $this->fareCalculatorService->calculateFare($fareData);
            $this->response->success('Fare calculated successfully.', $fareBreakdown);
        } catch (ValidationException $e) {
            $this->response->error($e->getMessage(), 422, $e->getErrors());
        } catch (NotFoundException $e) {
            $this->response->error($e->getMessage(), 404);
        } catch (\Exception $e) {
            error_log("FareCalculatorController error in calculate: " . $e->getMessage());
            $this->response->error("Failed to calculate fare: " . $e->getMessage(), 500);
        }
    }
}