<?php

namespace App\Services;

use App\Repositories\Contracts\LocationInterface;

class LocationService
{
    private LocationInterface $locationRepository;
    private string $googlePlacesApiBaseUrl = "https://maps.googleapis.com/maps/api/place/autocomplete/json";

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
    public function getSuggestions(string $input, string $sessionToken, string $language = 'en', string $types = ''): array
    {
        if (strlen($input) < 2) {
            throw new \InvalidArgumentException("Input must be at least 2 characters long.");
        }

        $googleApiKey = $this->locationRepository->getLatestActiveKey();

        if (!$googleApiKey) {
            throw new \Exception("Google API Key not found or not active.");
        }

        // Build the API request URL
        $params = [
            'input' => $input,
            'key' => $googleApiKey->apiKey,
            'sessiontoken' => $sessionToken,
            'language' => $language,
        ];

        if (!empty($types)) {
            $params['types'] = $types;
        }

        $url = $this->googlePlacesApiBaseUrl . '?' . http_build_query($params);

        // Make the API call using cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new \Exception("cURL error: " . $error);
        }

        $responseData = json_decode($response, true);

        if ($httpCode !== 200 || json_last_error() !== JSON_ERROR_NONE) {
            $errorMessage = $responseData['error_message'] ?? 'Unknown API error';
            throw new \Exception("Google Places API error (HTTP {$httpCode}): " . $errorMessage);
        }

        // Extract and return relevant suggestions
        $suggestions = [];
        if (isset($responseData['predictions']) && is_array($responseData['predictions'])) {
            foreach ($responseData['predictions'] as $prediction) {
                $suggestions[] = [
                    'place_id' => $prediction['place_id'],
                    'description' => $prediction['description'],
                    'matched_substrings' => $prediction['matched_substrings'] ?? [],
                    'terms' => $prediction['terms'] ?? []
                ];
            }
        }

        return $suggestions;
    }
}