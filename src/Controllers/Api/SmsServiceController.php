<?php

namespace App\Controllers\Api;

use App\Http\Request;
use App\Http\Response;
use App\Services\SmsServiceService;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Http\Requests\Api\Sms\CreateRequest;
use App\Http\Requests\Api\Sms\UpdateRequest;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "SMS Services", description: "API for managing SMS service phone numbers.")]
class SmsServiceController
{
    private SmsServiceService $smsServiceService;
    private Request $request;
    private Response $response;

    public function __construct(SmsServiceService $smsServiceService, Request $request, Response $response)
    {
        $this->smsServiceService = $smsServiceService;
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Get all SMS service entries.
     * GET /api/v1/sms-services
     */
    #[OA\Get(
        path: "/sms-services",
        summary: "Get a list of all SMS service entries",
        tags: ["SMS Services"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(ref: "#/components/schemas/SmsService")
                )
            ),
            new OA\Response(
                response: 500,
                description: "Internal Server Error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Failed to retrieve SMS services due to a database error.")
                    ]
                )
            )
        ]
    )]
    public function index(): void
    {
        try {
            $smsServices = $this->smsServiceService->getAllSmsServices();
            $smsServicesArray = array_map(fn($ss) => $ss->toArray(), $smsServices);
            $this->response->success('SMS services retrieved successfully.', $smsServicesArray, 200);
        } catch (\Exception $e) {
            error_log("SmsServiceController error in index: " . $e->getMessage());
            $this->response->error("Failed to retrieve SMS services: " . $e->getMessage(), 500);
        }
    }

    /**
     * Get a single SMS service entry by ID.
     * GET /api/v1/sms-services/{id}
     */
    #[OA\Get(
        path: "/sms-services/{id}",
        summary: "Get a single SMS service entry by ID",
        tags: ["SMS Services"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID of the SMS service entry to retrieve",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", format: "int64", example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation",
                content: new OA\JsonContent(ref: "#/components/schemas/SmsService")
            ),
            new OA\Response(
                response: 404,
                description: "SMS service entry not found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "SMS Service with ID 1 not found.")
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Internal Server Error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Failed to retrieve SMS service due to a database error.")
                    ]
                )
            )
        ]
    )]
    public function show(int $id): void
    {
        try {
            $smsService = $this->smsServiceService->getSmsServiceById($id);
            $this->response->success('SMS service retrieved successfully.', $smsService->toArray(), 200);
        } catch (NotFoundException $e) {
            $this->response->error($e->getMessage(), 404);
        } catch (\Exception $e) {
            error_log("SmsServiceController error in show: " . $e->getMessage());
            $this->response->error("Failed to retrieve SMS service: " . $e->getMessage(), 500);
        }
    }

    /**
     * Create a new SMS service entry.
     * POST /api/v1/sms-services
     */
    #[OA\Post(
        path: "/sms-services",
        summary: "Create a new SMS service entry",
        tags: ["SMS Services"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/CreateSmsServiceRequest")
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "SMS service entry created successfully",
                content: new OA\JsonContent(ref: "#/components/schemas/SmsService")
            ),
            new OA\Response(
                response: 422,
                description: "Validation error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "The given data was invalid."),
                        new OA\Property(property: "errors", type: "object", example: ["phone_number" => ["Phone number is required."]])
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Internal Server Error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Failed to create SMS service due to a database error.")
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

            $smsService = $this->smsServiceService->createSmsService($validatedData);
            $this->response->success('SMS service created successfully.', $smsService->toArray(), 201);
        } catch (ValidationException $e) {
            $this->response->error($e->getMessage(), 422, $e->getErrors());
        } catch (\Exception $e) {
            error_log("SmsServiceController error in store: " . $e->getMessage());
            $this->response->error("Failed to create SMS service: " . $e->getMessage(), 500);
        }
    }

    /**
     * Update an existing SMS service entry.
     * PUT /api/v1/sms-services/{id}
     */
    #[OA\Put(
        path: "/sms-services/{id}",
        summary: "Update an existing SMS service entry",
        tags: ["SMS Services"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID of the SMS service entry to update",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", format: "int64", example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/UpdateSmsServiceRequest")
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "SMS service entry updated successfully",
                content: new OA\JsonContent(ref: "#/components/schemas/SmsService")
            ),
            new OA\Response(
                response: 404,
                description: "SMS service entry not found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "SMS Service with ID 1 not found.")
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
                        new OA\Property(property: "errors", type: "object", example: ["phone_number" => ["Phone number format is invalid."]])
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Internal Server Error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Failed to update SMS service due to a database error.")
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

            $smsService = $this->smsServiceService->updateSmsService($id, $validatedData);
            $this->response->success('SMS service updated successfully.', $smsService->toArray(), 200);
        } catch (NotFoundException $e) {
            $this->response->error($e->getMessage(), 404);
        } catch (ValidationException $e) {
            $this->response->error($e->getMessage(), 422, $e->getErrors());
        } catch (\Exception $e) {
            error_log("SmsServiceController error in update: " . $e->getMessage());
            $this->response->error("Failed to update SMS service: " . $e->getMessage(), 500);
        }
    }

    /**
     * Delete an SMS service entry by ID.
     * DELETE /api/v1/sms-services/{id}
     */
    #[OA\Delete(
        path: "/sms-services/{id}",
        summary: "Delete an SMS service entry by ID",
        tags: ["SMS Services"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID of the SMS service entry to delete",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", format: "int64", example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "SMS service entry deleted successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "SMS service deleted successfully.")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "SMS service entry not found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "SMS Service with ID 1 not found.")
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Internal Server Error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Failed to delete SMS service due to a database error.")
                    ]
                )
            )
        ]
    )]
    public function destroy(int $id): void
    {
        try {
            $this->smsServiceService->deleteSmsService($id);
            $this->response->success('SMS service deleted successfully.', ['success' => true], 200);
        } catch (NotFoundException $e) {
            $this->response->error($e->getMessage(), 404);
        } catch (\Exception $e) {
            error_log("SmsServiceController error in destroy: " . $e->getMessage());
            $this->response->error("Failed to delete SMS service: " . $e->getMessage(), 500);
        }
    }
}