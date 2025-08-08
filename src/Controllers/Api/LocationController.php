<?php

namespace App\Controllers\Api;

use App\Http\Request;
use App\Http\Response;
use App\Services\LocationService;
use App\Exceptions\ValidationException;
use OpenApi\Attributes as OA;

class LocationController
{
    private LocationService $locationService;
    private Request $request;
    private Response $response;

    public function __construct(LocationService $locationService, Request $request, Response $response)
    {
        $this->locationService = $locationService;
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Get location suggestions based on user input.
     * GET /api/v1/locations/suggest
     */
    #[OA\Get(
        path: "/locations/suggest",
        summary: "Get location suggestions based on user input (at least 2 characters)",
        tags: ["Locations"],
        parameters: [
            new OA\Parameter(
                name: "input",
                in: "query",
                required: true,
                description: "The partial location string (at least 2 characters) for which to get suggestions.",
                schema: new OA\Schema(type: "string", minLength: 2, example: "Na")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Location suggestions retrieved successfully"),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                type: "object",
                                properties: [
                                    new OA\Property(property: "place_id", type: "string", example: "ChIJ7cv00DwsDogRAMDACuHnQJQ"),
                                    new OA\Property(property: "description", type: "string", example: "Nashville, TN, USA"),
                                    new OA\Property(property: "matched_substrings", type: "array", items: new OA\Items(type: "object")),
                                    new OA\Property(property: "terms", type: "array", items: new OA\Items(type: "object"))
                                ]
                            )
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: "Bad Request (e.g., input too short)",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Input must be at least 2 characters long.")
                    ]
                )
            ),
            new OA\Response(response: 500, description: "Internal Server Error")
        ]
    )]
    public function suggest(): void
    {
        try {
            $input = $this->request->input('input');

            // Validate input length
            if (!$input || strlen($input) < 2) {
                $this->response->error("Input must be at least 2 characters long for location suggestions.", 400);
                return;
            }

            $rawSuggestions = $this->locationService->getSuggestions($input);

            // Debug point: Remove this after testing
            //echo '<pre>';
            //print_r($rawSuggestions);
            //echo '</pre>';

            // Handle possible nested structure
            $suggestions = $rawSuggestions['suggestions']
                ?? $rawSuggestions['data']['suggestions']
                ?? null;

            if (!$suggestions || !is_array($suggestions)) {
                $this->response->error("Google Places API error: suggestions not found.", 500);
                return;
            }

            $mapped = [];
            foreach ($suggestions as $s) {
                $place = $s['placePrediction'] ?? [];

                $placeId = $place['placeId'] ?? null;
                $structured = $place['structuredFormat'] ?? [];

                $main = $structured['mainText']['text'] ?? '';
                $secondary = $structured['secondaryText']['text'] ?? '';

                $description = trim($main . ($secondary ? ', ' . $secondary : ''));

                $mapped[] = [
                    'place_id' => $placeId,
                    'description' => $description,
                    'matched_substrings' => $structured['mainText']['matches'] ?? [],
                    'terms' => $this->_buildTermsArray($description),
                ];
            }

            $this->response->success("Location suggestions retrieved successfully", $mapped);
        } catch (\InvalidArgumentException $e) {
            $this->response->error($e->getMessage(), 400);
        } catch (\Throwable $e) {
            error_log("LocationController error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
            $this->response->error("Google Places API error (HTTP 200): " . $e->getMessage(), 500);
        }
    }


    /**
     * Split description into terms for OpenAPI-compatible structure
     *
     * @param string $description
     * @return array
     */
    private function _buildTermsArray(string $description): array
    {
        $parts = explode(',', $description);
        $terms = [];

        foreach ($parts as $part) {
            $terms[] = ['value' => trim($part)];
        }

        return $terms;
    }
}
