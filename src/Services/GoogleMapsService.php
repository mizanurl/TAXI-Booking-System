<?php

namespace App\Services;

use App\Services\GoogleApiKeyService;
use Exception;

class GoogleMapsService
{
    private const DISTANCE_MATRIX_API_URL = "https://maps.googleapis.com/maps/api/distancematrix/json?";
    private const GEOCODING_API_URL = "https://maps.googleapis.com/maps/api/geocode/json?";

    private GoogleApiKeyService $googleApiKeyService;
    private ?string $apiKey = null;

    public function __construct(GoogleApiKeyService $googleApiKeyService)
    {
        $this->googleApiKeyService = $googleApiKeyService;
    }

    public function getTravelDetails(array $origins, array $destinations): ?array
    {
        $apiKey = $this->getApiKey();

        if (!$apiKey) {
            return null;
        }

        $originsString = implode('|', array_map(function ($loc) {
            return "place_id:{$loc['placeId']}";
        }, $origins));

        $destinationsString = implode('|', array_map(function ($loc) {
            return "place_id:{$loc['placeId']}";
        }, $destinations));

        $url = self::DISTANCE_MATRIX_API_URL . http_build_query([
            'origins' => $originsString,
            'destinations' => $destinationsString,
            'key' => $apiKey,
        ]);

        $response = @file_get_contents($url);

        if ($response === false) {
            return null; // Or throw an exception
        }

        $data = json_decode($response, true);

        if ($data['status'] !== 'OK') {
            return null; // Or throw an exception
        }

        return $data;
    }

    /**
     * Get place details (place ID, lat, lng) for a given address string using Geocoding API.
     * @param string $address
     * @return array|null
     * @throws Exception
     */
    public function getPlaceDetailsByAddress(string $address): ?array
    {
        try {
            $apiKey = $this->getApiKey();
            $url = self::GEOCODING_API_URL . "address=" . urlencode($address) . "&key=" . $apiKey;
            $response = @file_get_contents($url);

            if ($response === false) {
                throw new Exception("Failed to fetch data from Geocoding API.");
            }

            $data = json_decode($response, true);

            if ($data['status'] === 'OK' && !empty($data['results'])) {
                $result = $data['results'][0];
                return [
                    'place_id' => $result['place_id'],
                    'latitude' => $result['geometry']['location']['lat'],
                    'longitude' => $result['geometry']['location']['lng']
                ];
            }

            // Return null if no results are found or the status is not 'OK'
            return null;
        } catch (Exception $e) {
            error_log("Error in getPlaceDetailsByAddress: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Retrieves the active Google API key from the service.
     * @return string|null
     * @throws Exception If no active API key is found.
     */
    private function getApiKey(): ?string
    {
        if ($this->apiKey === null) {
            $activeKey = $this->googleApiKeyService->getOneActiveKey();
            if (!$activeKey) {
                throw new Exception("No active Google API key found for Google Maps Service.");
            }
            $this->apiKey = $activeKey->apiKey;
        }
        return $this->apiKey;
    }

    /**
     * Fetches distance and duration from Google Distance Matrix API.
     * This is a placeholder/mock implementation. In a real scenario, you'd make an actual HTTP request.
     *
     * @param float $originLat
     * @param float $originLng
     * @param float $destinationLat
     * @param float $destinationLng
     * @return array Contains 'distance' (in meters) and 'duration' (in seconds).
     * @throws Exception If API key is missing or API call fails.
     */
    /*public function getDistanceMatrix(float $originLat, float $originLng, float $destinationLat, float $destinationLng): array
    {
        try {
            $apiKey = $this->getApiKey();
            if (!$apiKey) {
                throw new Exception("Google Maps API Key is not available for Distance Matrix calculation.");
            }

            // In a real application, you would use a robust HTTP client (e.g., Guzzle)
            // to make a request to Google's Distance Matrix API here.
            // Example URL:
            // $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins={$originLat},{$originLng}&destinations={$destinationLat},{$destinationLng}&key={$apiKey}";
            // $response = file_get_contents($url); // Use a proper HTTP client in production
            // $data = json_decode($response, true);
            // // Parse $data to get distance and duration from the response.
            // // Handle errors and status codes from Google API.

            // For now, return mock data for demonstration
            // You can adjust these values for testing different scenarios
            $distanceMeters = 15000; // Example: 15 km
            $durationSeconds = 900;  // Example: 15 minutes

            return [
                'distance' => $distanceMeters,
                'duration' => $durationSeconds,
            ];
        } catch (Exception $e) {
            error_log("GoogleMapsService error in getDistanceMatrix: " . $e->getMessage());
            throw new Exception("Failed to get distance matrix: " . $e->getMessage());
        }
    }*/

    /**
     * Get distance and time from Google Maps Distance Matrix API.
     *
     * @param string $originName
     * @param string $destinationName
     * @return array
     */
    public function getDistanceMatrix(string $originName, string $destinationName): array
    {
        
        $apiKey = $this->getApiKey();

        $url = 'https://routes.googleapis.com/directions/v2:computeRoutes';

        $postData = [
            "origin" => [
                "address" => $originName
            ],
            "destination" => [
                "address" => $destinationName
            ],
            "travelMode" => "DRIVE",
            "routingPreference" => "TRAFFIC_AWARE",
            "units" => "IMPERIAL"
        ];

        $headers = [
            "Content-Type: application/json",
            "X-Goog-Api-Key: {$apiKey}",
            "X-Goog-FieldMask: routes.duration,routes.distanceMeters"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        if (isset($data['routes'][0])) {
            $route = $data['routes'][0];
            $distanceMiles = round($route['distanceMeters'] / 1609.34, 2); // Convert meters to miles

            $durationSeconds = $this->parseDuration($route['duration']);
            $hours = floor($durationSeconds / 3600);
            $minutes = floor(($durationSeconds % 3600) / 60);

            $durationFormatted = sprintf('%d Hours %02d Minutes', $hours, $minutes);

            //echo "<pre>";
            //print_r("Distance: " . $distanceMiles . ' Miles; ');
            //print_r("Duration: " . $durationHours . ' Hours');
            //echo "</pre>";

            return [
                'distance' => $distanceMiles,
                'duration' => $durationFormatted
            ];
        }

        return [
            'distance' => 0,
            'duration' => 0
        ];
    }

    private function parseDuration(string $duration): int
    {
        // e.g., "3600s"
        return (int) str_replace('s', '', $duration);
    }
}
