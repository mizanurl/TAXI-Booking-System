<?php

namespace App\Controllers\Api;

use App\Http\Request;
use App\Http\Response;
use App\Services\CommonSettingService;
use App\Exceptions\ValidationException;
use App\Exceptions\DuplicateEntryException;
use App\Exceptions\NotFoundException;
use App\Http\Requests\CommonSetting\CreateRequest as CreateCommonSettingRequest;
use App\Http\Requests\CommonSetting\UpdateRequest as UpdateCommonSettingRequest;
use OpenApi\Attributes as OA;

class CommonSettingController
{
    private CommonSettingService $commonSettingService;
    private Request $request;
    private Response $response;

    public function __construct(CommonSettingService $commonSettingService, Request $request, Response $response)
    {
        $this->commonSettingService = $commonSettingService;
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Get the single common settings record.
     * GET /api/v1/settings
     */
    #[OA\Get(
        path: "/settings",
        summary: "Get the single common settings record",
        tags: ["Common Settings"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Common settings retrieved successfully"),
                        new OA\Property(property: "data", ref: "#/components/schemas/CommonSetting")
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Common settings not found"),
            new OA\Response(response: 500, description: "Internal Server Error")
        ]
    )]
    public function get(): void
    {
        try {
            $settings = $this->commonSettingService->getCommonSettings();
            if ($settings) {
                $this->response->success('Common settings retrieved successfully', $settings->toArray());
            } else {
                $this->response->error('Common settings not found. Please create them first.', 404);
            }
        } catch (\Throwable $e) {
            error_log("CommonSettingController error in get: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
            $this->response->error('Failed to retrieve common settings: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create the initial common settings record.
     * POST /api/v1/settings
     * This endpoint should only be called once to initialize settings.
     */
    #[OA\Post(
        path: "/settings",
        summary: "Create the initial common settings record",
        tags: ["Common Settings"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data", // Use multipart/form-data for file upload
                schema: new OA\Schema(ref: "#/components/schemas/CreateCommonSettingRequest")
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Common settings created successfully",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Common settings created successfully"),
                        new OA\Property(property: "data", ref: "#/components/schemas/CommonSetting")
                    ]
                )
            ),
            new OA\Response(
                response: 409,
                description: "Conflict - Common settings already exist.",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Common settings already exist. Use the update API instead.")
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation error",
                content: new OA\JsonContent(
                    type: "object",
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
    public function create(): void
    {
        try {
            $createRequest = new CreateCommonSettingRequest($this->request);
            $validatedData = $createRequest->validate();
            $fileData = $this->request->file('company_logo'); // Get file data

            error_log("CommonSettingController - Validated Data: " . print_r($validatedData, true));
            error_log("CommonSettingController - File Data: " . print_r($fileData, true));

            $settings = $this->commonSettingService->createCommonSettings($validatedData, $fileData);
            $this->response->success('Common settings created successfully', $settings->toArray(), 201);
        } catch (ValidationException $e) {
            $this->response->error($e->getMessage(), 422, $e->getErrors());
        } catch (DuplicateEntryException $e) {
            $this->response->error($e->getMessage(), 409);
        } catch (\InvalidArgumentException $e) { // Catch validation for image dimensions
            $this->response->error($e->getMessage(), 422);
        } catch (\Throwable $e) {
            error_log("CommonSettingController error in create: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
            $this->response->error('Failed to create common settings: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update the existing common settings record.
     * PUT /api/v1/settings
     */
    #[OA\Put(
        path: "/settings",
        summary: "Update the existing common settings record",
        tags: ["Common Settings"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data", // Use multipart/form-data for file upload
                schema: new OA\Schema(ref: "#/components/schemas/UpdateCommonSettingRequest")
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Common settings updated successfully",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Common settings updated successfully"),
                        new OA\Property(property: "data", ref: "#/components/schemas/CommonSetting")
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Common settings not found"),
            new OA\Response(
                response: 422,
                description: "Validation error",
                content: new OA\JsonContent(
                    type: "object",
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
    public function update(): void
    {
        try {
            $updateRequest = new UpdateCommonSettingRequest($this->request);
            $validatedData = $updateRequest->validate();
            $fileData = $this->request->file('company_logo'); // Get file data

            error_log("CommonSettingController - Validated Data for Update: " . print_r($validatedData, true));
            error_log("CommonSettingController - File Data for Update: " . print_r($fileData, true));

            $settings = $this->commonSettingService->updateCommonSettings($validatedData, $fileData);
            $this->response->success('Common settings updated successfully', $settings->toArray());
        } catch (ValidationException $e) {
            $this->response->error($e->getMessage(), 422, $e->getErrors());
        } catch (NotFoundException $e) {
            $this->response->error($e->getMessage(), 404);
        } catch (\InvalidArgumentException $e) { // Catch validation for image dimensions
            $this->response->error($e->getMessage(), 422);
        } catch (\Throwable $e) {
            error_log("CommonSettingController error in update: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
            $this->response->error('Failed to update common settings: ' . $e->getMessage(), 500);
        }
    }
}