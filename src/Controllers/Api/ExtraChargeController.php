<?php

namespace App\Controllers\Api;

use App\Http\Request;
use App\Http\Response;
use App\Services\ExtraChargeService;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Http\Requests\Api\ExtraCharge\CreateRequest;
use App\Http\Requests\Api\ExtraCharge\UpdateRequest;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Extra Charges / Toll", description: "API for managing extra charges and tolls based on areas/zip codes.")]
class ExtraChargeController
{
    private ExtraChargeService $extraChargeService;
    private Request $request;
    private Response $response;

    public function __construct(ExtraChargeService $extraChargeService, Request $request, Response $response)
    {
        $this->extraChargeService = $extraChargeService;
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Get all extra charges.
     * GET /api/v1/extra-charges
     */
    #[OA\Get(
        path: "/extra-charges",
        summary: "Get a list of all extra charges",
        tags: ["Extra Charges / Toll"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(ref: "#/components/schemas/ExtraCharge")
                )
            ),
            new OA\Response(
                response: 500,
                description: "Internal Server Error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Failed to retrieve extra charges due to a database error.")
                    ]
                )
            )
        ]
    )]
    public function index(): void
    {
        try {
            $extraCharges = $this->extraChargeService->getAllExtraCharges();
            $extraChargesArray = array_map(fn($ec) => $ec->toArray(), $extraCharges);
            $this->response->success('Extra charges retrieved successfully.', $extraChargesArray, 200);
        } catch (\Exception $e) {
            error_log("ExtraChargeController error in index: " . $e->getMessage());
            $this->response->error("Failed to retrieve extra charges: " . $e->getMessage(), 500);
        }
    }

    /**
     * Get a single extra charge by ID.
     * GET /api/v1/extra-charges/{id}
     */
    #[OA\Get(
        path: "/extra-charges/{id}",
        summary: "Get a single extra charge by ID",
        tags: ["Extra Charges / Toll"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID of the extra charge to retrieve",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", format: "int64", example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation",
                content: new OA\JsonContent(ref: "#/components/schemas/ExtraCharge")
            ),
            new OA\Response(
                response: 404,
                description: "Extra charge not found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Extra Charge with ID 1 not found.")
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Internal Server Error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Failed to retrieve extra charge due to a database error.")
                    ]
                )
            )
        ]
    )]
    public function show(int $id): void
    {
        try {
            $extraCharge = $this->extraChargeService->getExtraChargeById($id);
            $this->response->success('Extra charge retrieved successfully.', $extraCharge->toArray(), 200);
        } catch (NotFoundException $e) {
            $this->response->error($e->getMessage(), 404);
        } catch (\Exception $e) {
            error_log("ExtraChargeController error in show: " . $e->getMessage());
            $this->response->error("Failed to retrieve extra charge: " . $e->getMessage(), 500);
        }
    }

    /**
     * Create a new extra charge.
     * POST /api/v1/extra-charges
     */
    #[OA\Post(
        path: "/extra-charges",
        summary: "Create a new extra charge",
        tags: ["Extra Charges / Toll"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/CreateExtraChargeRequest")
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Extra charge created successfully",
                content: new OA\JsonContent(ref: "#/components/schemas/ExtraCharge")
            ),
            new OA\Response(
                response: 422,
                description: "Validation error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "The given data was invalid."),
                        new OA\Property(property: "errors", type: "object", example: ["area_name" => ["Area name is required."]])
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Internal Server Error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Failed to create extra charge due to a database error.")
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

            $extraCharge = $this->extraChargeService->createExtraCharge($validatedData);
            $this->response->success('Extra charge created successfully.', $extraCharge->toArray(), 201);
        } catch (ValidationException $e) {
            $this->response->error($e->getMessage(), 422, $e->getErrors());
        } catch (\Exception $e) {
            error_log("ExtraChargeController error in store: " . $e->getMessage());
            $this->response->error("Failed to create extra charge: " . $e->getMessage(), 500);
        }
    }

    /**
     * Update an existing extra charge.
     * PUT /api/v1/extra-charges/{id}
     */
    #[OA\Put(
        path: "/extra-charges/{id}",
        summary: "Update an existing extra charge",
        tags: ["Extra Charges / Toll"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID of the extra charge to update",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", format: "int64", example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/UpdateExtraChargeRequest")
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Extra charge updated successfully",
                content: new OA\JsonContent(ref: "#/components/schemas/ExtraCharge")
            ),
            new OA\Response(
                response: 404,
                description: "Extra charge not found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Extra Charge with ID 1 not found.")
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
                        new OA\Property(property: "errors", type: "object", example: ["extra_charge" => ["Extra charge must be at least 0."]])
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Internal Server Error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Failed to update extra charge due to a database error.")
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

            $extraCharge = $this->extraChargeService->updateExtraCharge($id, $validatedData);
            $this->response->success('Extra charge updated successfully.', $extraCharge->toArray(), 200);
        } catch (NotFoundException $e) {
            $this->response->error($e->getMessage(), 404);
        } catch (ValidationException $e) {
            $this->response->error($e->getMessage(), 422, $e->getErrors());
        } catch (\Exception $e) {
            error_log("ExtraChargeController error in update: " . $e->getMessage());
            $this->response->error("Failed to update extra charge: " . $e->getMessage(), 500);
        }
    }

    /**
     * Delete an extra charge by ID.
     * DELETE /api/v1/extra-charges/{id}
     */
    #[OA\Delete(
        path: "/extra-charges/{id}",
        summary: "Delete an extra charge by ID",
        tags: ["Extra Charges / Toll"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID of the extra charge to delete",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", format: "int64", example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Extra charge deleted successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Extra charge deleted successfully.")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Extra charge not found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Extra Charge with ID 1 not found.")
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Internal Server Error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Failed to delete extra charge due to a database error.")
                    ]
                )
            )
        ]
    )]
    public function destroy(int $id): void
    {
        try {
            $this->extraChargeService->deleteExtraCharge($id);
            $this->response->success('Extra charge deleted successfully.', ['success' => true], 200);
        } catch (NotFoundException $e) {
            $this->response->error($e->getMessage(), 404);
        } catch (\Exception $e) {
            error_log("ExtraChargeController error in destroy: " . $e->getMessage());
            $this->response->error("Failed to delete extra charge: " . $e->getMessage(), 500);
        }
    }
}