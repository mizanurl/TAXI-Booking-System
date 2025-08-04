<?php

namespace App\Controllers\Api;

use App\Http\Request;
use App\Services\SlabService;
use App\Http\Response; // Ensure Response is imported
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Http\Requests\Api\Slab\CreateRequest;
use App\Http\Requests\Api\Slab\UpdateRequest;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Slab Settings", description: "API for managing pricing slabs.")]
class SlabController
{
    private SlabService $slabService;
    private Request $request;
    private Response $response;

    public function __construct(SlabService $slabService, Request $request, Response $response)
    {
        $this->slabService = $slabService;
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @param int $id
     * @return void
     */
    #[OA\Get(
        path: "/slabs/{id}",
        summary: "Get a single slab by ID",
        tags: ["Slab Settings"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID of the slab to retrieve",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", format: "int64", example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation",
                content: new OA\JsonContent(ref: "#/components/schemas/Slab")
            ),
            new OA\Response(
                response: 404,
                description: "Slab not found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Slab with ID 1 not found.")
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Internal Server Error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Failed to retrieve slab due to a database error.")
                    ]
                )
            )
        ]
    )]
    public function show(int $id): void
    {
        try {
            $slab = $this->slabService->getSlab($id);
            $this->response->success('Slab retrieved successfully', $slab->toArray(), 200);
        } catch (NotFoundException $e) {
            $this->response->error($e->getMessage(), 404); // Consistent return
        } catch (\Exception $e) {
            error_log("SlabController error in show: " . $e->getMessage());
            $this->response->error("Failed to retrieve slab: " . $e->getMessage(), 500);
        }
    }

    /**
     * @return void
     */
    #[OA\Get(
        path: "/slabs",
        summary: "Get a list of all slabs",
        tags: ["Slab Settings"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(ref: "#/components/schemas/Slab")
                )
            ),
            new OA\Response(
                response: 500,
                description: "Internal Server Error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Failed to retrieve slabs due to a database error.")
                    ]
                )
            )
        ]
    )]
    public function index(): void
    {
        try {
            $slabs = $this->slabService->getAllSlabs();
            $slabsArray = array_map(fn($slab) => $slab->toArray(), $slabs);
            $this->response->success('Slabs retrieved successfully', $slabsArray, 200);
        } catch (\Exception $e) {
            error_log("SlabController error in index: " . $e->getMessage());
            $this->response->error("Failed to retrieve slabs: " . $e->getMessage(), 500);
        }
    }

    /**
     * @return void
     */
    #[OA\Post(
        path: "/slabs",
        summary: "Create a new slab",
        tags: ["Slab Settings"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/SlabCreateRequest")
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Slab created successfully",
                content: new OA\JsonContent(ref: "#/components/schemas/Slab")
            ),
            new OA\Response(
                response: 422,
                description: "Validation error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "The given data was invalid."),
                        new OA\Property(property: "errors", type: "object", example: ["slab_value" => ["Slab value is required."]])
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Internal Server Error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Failed to create slab due to a database error.")
                    ]
                )
            )
        ]
    )]
    public function store(): void
    {
        try {
            $createRequest = new CreateRequest($this->request);
            $validatedData = $createRequest->validate();

            $slab = $this->slabService->createSlab($validatedData);
            $this->response->success('Slab created successfully', $slab->toArray(), 201);
        } catch (ValidationException $e) {
            $this->response->error($e->getMessage(), 422, $e->getErrors());
        } catch (\Exception $e) {
            error_log("SlabController error in store: " . $e->getMessage());
            $this->response->error("Failed to create slab: " . $e->getMessage(), 500);
        }
    }

    /**
     * @param int $id
     * @return void
     */
    #[OA\Put(
        path: "/slabs/{id}",
        summary: "Update an existing slab",
        tags: ["Slab Settings"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID of the slab to update",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", format: "int64", example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/SlabUpdateRequest")
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Slab updated successfully",
                content: new OA\JsonContent(ref: "#/components/schemas/Slab")
            ),
            new OA\Response(
                response: 404,
                description: "Slab not found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Slab with ID 1 not found.")
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "The given data was invalid."),
                        new OA\Property(property: "errors", type: "object", example: ["slab_value" => ["Slab value must be a number."]])
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Internal Server Error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Failed to update slab due to a database error.")
                    ]
                )
            )
        ]
    )]
    public function update(int $id): void
    {
        try {
            $updateRequest = new UpdateRequest($this->request);
            $validatedData = $updateRequest->validate();

            $slab = $this->slabService->updateSlab($id, $validatedData);
            $this->response->success('Slab updated successfully', $slab->toArray(), 200);
        } catch (NotFoundException $e) {
            $this->response->error($e->getMessage(), 404);
        } catch (ValidationException $e) {
            $this->response->error($e->getMessage(), 422, $e->getErrors());
        } catch (\Exception $e) {
            error_log("SlabController error in update: " . $e->getMessage());
            $this->response->error("Failed to update slab: " . $e->getMessage(), 500);
        }
    }

    /**
     * @param int $id
     * @return void
     */
    #[OA\Delete(
        path: "/slabs/{id}",
        summary: "Delete a slab by ID",
        tags: ["Slab Settings"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID of the slab to delete",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", format: "int64", example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Slab deleted successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Slab deleted successfully.")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Slab not found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Slab with ID 1 not found.")
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Internal Server Error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Failed to delete slab due to a database error.")
                    ]
                )
            )
        ]
    )]
    public function destroy(int $id): void
    {
        try {
            $this->slabService->deleteSlab($id);
            $this->response->success('Slab deleted successfully.', ['success' => true], 200);
        } catch (NotFoundException $e) {
            $this->response->error($e->getMessage(), 404);
        } catch (\Exception $e) {
            error_log("SlabController error in destroy: " . $e->getMessage());
            $this->response->error("Failed to delete slab: " . $e->getMessage(), 500);
        }
    }
}