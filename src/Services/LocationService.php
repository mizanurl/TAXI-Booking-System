<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use App\Repositories\Contracts\LocationInterface;

class LocationService
{
    private LocationInterface $locationRepository;
    //private string $googlePlacesApiBaseUrl = "https://maps.googleapis.com/maps/api/place/autocomplete/json";
    private string $googlePlacesApiBaseUrl = "https://places.googleapis.com/v1/places:autocomplete";

    public function __construct(LocationInterface $locationRepository)
    {
        $this->locationRepository = $locationRepository;
    }

    /**
     * Get location suggestions from Google Places API based on input.
     *
     * @param string $input The partial location string provided by the user.
     * @param string $sessionToken A session token to group autocomplete requests.
     * @param string $language The language code for results (e.g., 'en').
     * @param string $types Restrict results to specific types (e.g., 'geocode', 'address').
     * @return array An array of location suggestions.
     * @throws \Exception If API key is not found or API call fails.
     */
    public function getSuggestions(string $input): array
    {
        if (strlen($input) < 2) {
            throw new \InvalidArgumentException("Input must be at least 2 characters long.");
        }

        $googleApiKeyData = $this->locationRepository->getLatestActiveKey();

        if (!$googleApiKeyData) {
            throw new \Exception("Google API Key not found or not active.");
        }

        $googleApiKey = $googleApiKeyData->apiKey;

        $client = new Client();

        try {
            $response = $client->post('https://places.googleapis.com/v1/places:autocomplete', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-Goog-Api-Key' => $googleApiKey,
                    'X-Goog-FieldMask' => 'suggestions.placePrediction.placeId,suggestions.placePrediction.structuredFormat.mainText.text,suggestions.placePrediction.structuredFormat.secondaryText.text',
                ],
                'json' => [
                    'input' => $input,
                    'languageCode' => 'en',
                    'sessionToken' => bin2hex(random_bytes(10)),
                ],
                'http_errors' => false
            ]);

            $statusCode = $response->getStatusCode();
            $bodyContent = (string) $response->getBody();
            $body = json_decode($bodyContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'status' => 'error',
                    'message' => 'Invalid JSON from Google: ' . json_last_error_msg() . ' | Raw: ' . $bodyContent,
                ];
            }

            if ($statusCode === 200 && isset($body['suggestions'])) {
                return [
                    'status' => 'success',
                    'suggestions' => $body['suggestions']
                ];
            }

            return [
                'status' => 'error',
                'message' => 'Google Places API error (HTTP ' . $statusCode . '): ' . ($body['error']['message'] ?? 'Unknown error')
            ];
        } catch (RequestException $e) {
            return [
                'status' => 'error',
                'message' => 'HTTP Request failed: ' . $e->getMessage()
            ];
        }
    }
}
