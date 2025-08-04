<?php

namespace App\Controllers\Api;

use App\Http\Request;
use App\Http\Response;
use App\Services\GoogleApiKeyService;
use App\Exceptions\ValidationException;
use App\Exceptions\NotFoundException;

use OpenApi\Attributes as OA;

class GoogleApiKeyController
{
    private GoogleApiKeyService $googleApiKeyService;
    private Request $request;
    private Response $response;

    public function __construct(
        GoogleApiKeyService $googleApiKeyService,
        request $request,
        Response $response
    ) {
        $this->googleApiKeyService = $googleApiKeyService;
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Get all active keys.
     * GET /api/v1/google-api-keys/active
     */
    #[OA\Get(
        path: "/google-api-keys/active",
        summary: "Get a list of all active keys",
        tags: ["Google API Keys"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Active keys retrieved successfully"),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(ref: "#/components/schemas/GoogleApiKey")
                        )
                    ]
                )
            ),
            new OA\Response(response: 500, description: "Internal Server Error")
        ]
    )]
    public function activeKeys(): void
    {
        try {
            $keys = $this->googleApiKeyService->getActiveKeys();
            $this->response->success('Active keys retrieved successfully', array_map(fn($key) => $key->toArray(), $keys));
        } catch (\Throwable $e) {
            $this->response->error('Failed to retrieve active keys: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get one active Google API key.
     * GET /api/v1/google-api-keys/active/single
     */
    #[OA\Get(
        path: "/google-api-keys/active/single",
        summary: "Get one active Google API key",
        tags: ["Google API Keys"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Active key retrieved successfully"),
                        new OA\Property(property: "data", ref: "#/components/schemas/GoogleApiKey")
                    ]
                )
            ),
            new OA\Response(response: 404, description: "No active key found"),
            new OA\Response(response: 500, description: "Internal Server Error")
        ]
    )]
    public function getOneActive(): void
    {
        try {
            $key = $this->googleApiKeyService->getOneActiveKey();
            if ($key) {
                $this->response->success('Active key retrieved successfully', $key->toArray());
            } else {
                // Use NotFoundException for 404 as per your error handling
                throw new NotFoundException('No active Google API key found.');
            }
        } catch (NotFoundException $e) {
            $this->response->error($e->getMessage(), 404);
        } catch (\Throwable $e) {
            $this->response->error('Failed to retrieve active key: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get all keys.
     * GET /api/v1/google-api-keys
     */
    #[OA\Get(
        path: "/google-api-keys",
        summary: "Get a list of all keys (active and inactive)",
        tags: ["Google API Keys"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Keys retrieved successfully"),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(ref: "#/components/schemas/GoogleApiKey")
                        )
                    ]
                )
            ),
            new OA\Response(response: 500, description: "Internal Server Error")
        ]
    )]
    public function index(): void
    {
        try {
            $keys = $this->googleApiKeyService->getAllKeys();
            $this->response->success('Keys retrieved successfully', array_map(fn($key) => $key->toArray(), $keys));
        } catch (\Throwable $e) {
            $this->response->error('Failed to retrieve keys: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get a single key by ID.
     * GET /api/v1/google-api-keys/{id}
     * @param int $id
     */
    #[OA\Get(
        path: "/google-api-keys/{id}",
        summary: "Get key details by ID",
        tags: ["Google API Keys"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID of the key to retrieve",
                schema: new OA\Schema(type: "integer")
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
                        new OA\Property(property: "message", type: "string", example: "Key retrieved successfully"),
                        new OA\Property(property: "data", ref: "#/components/schemas/GoogleApiKey")
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Key not found"),
            new OA\Response(response: 500, description: "Internal Server Error")
        ]
    )]
    public function show(int $id): void
    {
        try {
            $key = $this->googleApiKeyService->getKeyById($id);
            if ($key) {
                $this->response->success('Key retrieved successfully', $key->toArray());
            } else {
                $this->response->error('Key not found', 404);
            }
        } catch (\Throwable $e) {
            $this->response->error('Failed to retrieve key: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create a new key.
     * POST /api/v1/google-api-keys
     */
    #[OA\Post(
        path: "/google-api-keys",
        summary: "Create a new key",
        tags: ["Google API Keys"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/CreateGoogleApiKeyRequest")
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Key created successfully",
                content: new OA\JsonContent(
                    type: "array",
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Key created successfully"),
                        new OA\Property(property: "data", ref: "#/components/schemas/GoogleApiKey")
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation error",
                content: new OA\JsonContent(
                    type: "array",
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "The given data was invalid."),
                        new OA\Property(property: "errors", type: "object")
                    ]
                )
            ),
            new OA\Response(response: 500, description: "Internal Server Error")
        ]
    )]
    public function store(): void
    {
        try {
            // For simplicity, we'll use a basic validation here.
            // In a real app, you'd create a CreateAirportRequest class.
            $data = $this->request->all();
            if (empty($data['api_key']) || !isset($data['status'])) {
                throw new ValidationException("Missing required fields.", ['api_key', 'status']);
            }

            $airport = $this->googleApiKeyService->createKey($data);
            $this->response->success('Key created successfully', $airport->toArray(), 201);
        } catch (ValidationException $e) {
            $this->response->error($e->getMessage(), 422, $e->getErrors());
        } catch (\Throwable $e) {
            $this->response->error('Failed to create the key: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update an existing key.
     * PUT /api/v1/google-api-keys/{id}
     * @param int $id
     */
    #[OA\Put(
        path: "/google-api-keys/{id}",
        summary: "Update an existing key",
        tags: ["Google API Keys"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID of the key to update",
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/UpdateGoogleApiKeyRequest")
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Key updated successfully",
                content: new OA\JsonContent(
                    type: "array",
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Key updated successfully"),
                        new OA\Property(property: "data", ref: "#/components/schemas/GoogleApiKey")
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Key not found"),
            new OA\Response(response: 422, description: "Validation error"),
            new OA\Response(response: 500, description: "Internal Server Error")
        ]
    )]
    public function update(int $id): void
    {
        try {
            $data = $this->request->all();
            $airport = $this->googleApiKeyService->updateGoogleApiKey($id, $data);
            $this->response->success('Key updated successfully', $airport->toArray());
        } catch (ValidationException $e) {
            $this->response->error($e->getMessage(), 422, $e->getErrors());
        } catch (\Throwable $e) {
            $this->response->error('Failed to update the key: ' . $e->getMessage(), 500);
        }
    }
}