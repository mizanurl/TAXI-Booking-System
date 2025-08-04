<?php

namespace App\Controllers\Api;

use App\Http\Request;
use App\Http\Response;
use App\Services\TunnelChargeService;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Http\Requests\TunnelCharge\CreateRequest;
use App\Http\Requests\TunnelCharge\UpdateRequest;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Tunnel Charges", description: "API for managing tunnel charges.")]
class TunnelChargeController
{
    private TunnelChargeService $tunnelChargeService;
    private Request $request;
    private Response $response;

    public function __construct(TunnelChargeService $tunnelChargeService, Request $request, Response $response)
    {
        $this->tunnelChargeService = $tunnelChargeService;
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Get all tunnel charges.
     * GET /api/v1/tunnel-charges
     */
    #[OA\Get(
        path: "/tunnel-charges",
        summary: "Get a list of all tunnel charges",
        tags: ["Tunnel Charges"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(ref: "#/components/schemas/TunnelCharge")
                )
            ),
            new OA\Response(
                response: 500,
                description: "Internal Server Error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Failed to retrieve tunnel charges due to a database error.")
                    ]
                )
            )
        ]
    )]
    public function index(): void
    {
        try {
            $tunnelCharges = $this->tunnelChargeService->getAllTunnelCharges();
            $tunnelChargesArray = array_map(fn($tc) => $tc->toArray(), $tunnelCharges);
            $this->response->success('Tunnel charges retrieved successfully.', $tunnelChargesArray, 200);
        } catch (\Exception $e) {
            error_log("TunnelChargeController error in index: " . $e->getMessage());
            $this->response->error("Failed to retrieve tunnel charges: " . $e->getMessage(), 500);
        }
    }

    /**
     * Get a single tunnel charge by ID.
     * GET /api/v1/tunnel-charges/{id}
     */
    #[OA\Get(
        path: "/tunnel-charges/{id}",
        summary: "Get a single tunnel charge by ID",
        tags: ["Tunnel Charges"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID of the tunnel charge to retrieve",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", format: "int64", example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation",
                content: new OA\JsonContent(ref: "#/components/schemas/TunnelCharge")
            ),
            new OA\Response(
                response: 404,
                description: "Tunnel charge not found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Tunnel Charge with ID 1 not found.")
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Internal Server Error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Failed to retrieve tunnel charge due to a database error.")
                    ]
                )
            )
        ]
    )]
    public function show(int $id): void
    {
        try {
            $tunnelCharge = $this->tunnelChargeService->getTunnelChargeById($id);
            $this->response->success('Tunnel charge retrieved successfully.', $tunnelCharge->toArray(), 200);
        } catch (NotFoundException $e) {
            $this->response->error($e->getMessage(), 404);
        } catch (\Exception $e) {
            error_log("TunnelChargeController error in show: " . $e->getMessage());
            $this->response->error("Failed to retrieve tunnel charge: " . $e->getMessage(), 500);
        }
    }

    /**
     * Create a new tunnel charge.
     * POST /api/v1/tunnel-charges
     */
    #[OA\Post(
        path: "/tunnel-charges",
        summary: "Create a new tunnel charge",
        tags: ["Tunnel Charges"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/CreateTunnelChargeRequest")
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Tunnel charge created successfully",
                content: new OA\JsonContent(ref: "#/components/schemas/TunnelCharge")
            ),
            new OA\Response(
                response: 422,
                description: "Validation error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "The given data was invalid."),
                        new OA\Property(property: "errors", type: "object", example: ["charge_start_date" => ["Charge start date is required."]])
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Internal Server Error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Failed to create tunnel charge due to a database error.")
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

            $tunnelCharge = $this->tunnelChargeService->createTunnelCharge($validatedData);
            $this->response->success('Tunnel charge created successfully.', $tunnelCharge->toArray(), 201);
        } catch (ValidationException $e) {
            $this->response->error($e->getMessage(), 422, $e->getErrors());
        } catch (\Exception $e) {
            error_log("TunnelChargeController error in store: " . $e->getMessage());
            $this->response->error("Failed to create tunnel charge: " . $e->getMessage(), 500);
        }
    }

    /**
     * Update an existing tunnel charge.
     * PUT /api/v1/tunnel-charges/{id}
     */
    #[OA\Put(
        path: "/tunnel-charges/{id}",
        summary: "Update an existing tunnel charge",
        tags: ["Tunnel Charges"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID of the tunnel charge to update",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", format: "int64", example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/UpdateTunnelChargeRequest")
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Tunnel charge updated successfully",
                content: new OA\JsonContent(ref: "#/components/schemas/TunnelCharge")
            ),
            new OA\Response(
                response: 404,
                description: "Tunnel charge not found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Tunnel Charge with ID 1 not found.")
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
                        new OA\Property(property: "errors", type: "object", example: ["charge_amount" => ["Charge amount must be at least 0."]])
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Internal Server Error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Failed to update tunnel charge due to a database error.")
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

            $tunnelCharge = $this->tunnelChargeService->updateTunnelCharge($id, $validatedData);
            $this->response->success('Tunnel charge updated successfully.', $tunnelCharge->toArray(), 200);
        } catch (NotFoundException $e) {
            $this->response->error($e->getMessage(), 404);
        } catch (ValidationException $e) {
            $this->response->error($e->getMessage(), 422, $e->getErrors());
        } catch (\Exception $e) {
            error_log("TunnelChargeController error in update: " . $e->getMessage());
            $this->response->error("Failed to update tunnel charge: " . $e->getMessage(), 500);
        }
    }

    /**
     * Delete a tunnel charge by ID.
     * DELETE /api/v1/tunnel-charges/{id}
     */
    #[OA\Delete(
        path: "/tunnel-charges/{id}",
        summary: "Delete a tunnel charge by ID",
        tags: ["Tunnel Charges"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID of the tunnel charge to delete",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", format: "int64", example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Tunnel charge deleted successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Tunnel charge deleted successfully.")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Tunnel charge not found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Tunnel Charge with ID 1 not found.")
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Internal Server Error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Failed to delete tunnel charge due to a database error.")
                    ]
                )
            )
        ]
    )]
    public function destroy(int $id): void
    {
        try {
            $this->tunnelChargeService->deleteTunnelCharge($id);
            $this->response->success('Tunnel charge deleted successfully.', ['success' => true], 200);
        } catch (NotFoundException $e) {
            $this->response->error($e->getMessage(), 404);
        } catch (\Exception $e) {
            error_log("TunnelChargeController error in destroy: " . $e->getMessage());
            $this->response->error("Failed to delete tunnel charge: " . $e->getMessage(), 500);
        }
    }
}