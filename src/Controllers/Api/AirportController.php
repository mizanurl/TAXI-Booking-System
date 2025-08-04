<?php

namespace App\Controllers\Api;

use App\Http\Request;
use App\Http\Response;
use App\Services\AirportService;
use App\Exceptions\ValidationException;
use App\Exceptions\DuplicateEntryException;
use App\Exceptions\NotFoundException;

use OpenApi\Attributes as OA;

#[OA\Tag(name: "Airports", description: "API for managing airport locations.")]
#[OA\Schema(
    schema: "CreateAirportRequest",
    title: "CreateAirportRequest",
    properties: [
        new OA\Property(property: "name", type: "string", example: "New Airport Name"),
        new OA\Property(property: "description", type: "string", nullable: true, example: "Description of the new airport."),
        new OA\Property(property: "from_tax_toll", type: "number", format: "float", example: 10.00),
        new OA\Property(property: "to_tax_toll", type: "number", format: "float", example: 15.00),
        new OA\Property(property: "status", type: "integer", enum: [0, 1], example: 1)
    ],
    required: ["name", "from_tax_toll", "to_tax_toll", "status"]
)]
#[OA\Schema(
    schema: "UpdateAirportRequest",
    title: "UpdateAirportRequest",
    properties: [
        new OA\Property(property: "name", type: "string", example: "Updated Airport Name"),
        new OA\Property(property: "description", type: "string", nullable: true, example: "Updated description."),
        new OA\Property(property: "from_tax_toll", type: "number", format: "float", example: 12.00),
        new OA\Property(property: "to_tax_toll", type: "number", format: "float", example: 18.00),
        new OA\Property(property: "status", type: "integer", enum: [0, 1], example: 0)
    ]
)]
class AirportController
{
    private AirportService $airportService;
    private Request $request;
    private Response $response;

    public function __construct(
        AirportService $airportService,
        request $request,
        Response $response
        )
    {
        $this->airportService = $airportService;
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Get all active airports.
     * GET /api/v1/airports/active
     */
    #[OA\Get(
        path: "/airports/active",
        summary: "Get a list of all active airports",
        tags: ["Airports"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Active airports retrieved successfully"),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(ref: "#/components/schemas/Airport")
                        )
                    ]
                )
            ),
            new OA\Response(response: 500, description: "Internal Server Error")
        ]
    )]
    public function activeAirports(): void
    {
        try {
            $airports = $this->airportService->getActiveAirports();
            $this->response->success('Active airports retrieved successfully', array_map(fn($airport) => $airport->toArray(), $airports));
        } catch (\Throwable $e) {
            $this->response->error('Failed to retrieve active airports: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get all airports.
     * GET /api/v1/airports
     */
    #[OA\Get(
        path: "/airports",
        summary: "Get a list of all airports (active and inactive)",
        tags: ["Airports"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Airports retrieved successfully"),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(ref: "#/components/schemas/Airport")
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
            $airports = $this->airportService->getAllAirports();
            $this->response->success('Airports retrieved successfully', array_map(fn($airport) => $airport->toArray(), $airports));
        } catch (\Throwable $e) {
            $this->response->error('Failed to retrieve airports: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get a single airport by ID.
     * GET /api/v1/airports/{id}
     * @param int $id
     */
    #[OA\Get(
        path: "/airports/{id}",
        summary: "Get airport details by ID",
        tags: ["Airports"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID of the airport to retrieve",
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
                        new OA\Property(property: "message", type: "string", example: "Airport retrieved successfully"),
                        new OA\Property(property: "data", ref: "#/components/schemas/Airport")
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Airport not found"),
            new OA\Response(response: 500, description: "Internal Server Error")
        ]
    )]
    public function show(int $id): void
    {
        try {
            $airport = $this->airportService->getAirportById($id);
            if ($airport) {
                $this->response->success('Airport retrieved successfully', $airport->toArray());
            } else {
                $this->response->error('Airport not found', 404);
            }
        } catch (\Throwable $e) {
            $this->response->error('Failed to retrieve airport: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create a new airport.
     * POST /api/v1/airports
     */
    #[OA\Post(
        path: "/airports",
        summary: "Create a new airport",
        tags: ["Airports"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/CreateAirportRequest")
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Airport created successfully",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Airport created successfully"),
                        new OA\Property(property: "data", ref: "#/components/schemas/Airport")
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
    public function store(): void
    {
        try {
            $data = $this->request->all();
            // Basic validation. Consider using a dedicated FormRequest for more robust validation.
            if (empty($data['name']) || !isset($data['from_tax_toll']) || !isset($data['to_tax_toll']) || !isset($data['status'])) {
                throw new ValidationException("Missing required fields for airport creation.", ['name', 'from_tax_toll', 'to_tax_toll', 'status']);
            }

            $airport = $this->airportService->createAirport($data);
            $this->response->success('Airport created successfully', $airport->toArray(), 201);
        } catch (ValidationException $e) {
            $this->response->error($e->getMessage(), 422, $e->getErrors());
        } catch (DuplicateEntryException $e) { // Catch duplicate entry specifically
            $this->response->error($e->getMessage(), 409); // 409 Conflict for duplicates
        } catch (\Throwable $e) {
            $this->response->error('Failed to create airport: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update an existing airport.
     * PUT /api/v1/airports/{id}
     * @param int $id
     */
    #[OA\Put(
        path: "/airports/{id}",
        summary: "Update an existing airport",
        tags: ["Airports"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID of the airport to update",
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/UpdateAirportRequest")
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Airport updated successfully",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Airport updated successfully"),
                        new OA\Property(property: "data", ref: "#/components/schemas/Airport")
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Airport not found"),
            new OA\Response(response: 422, description: "Validation error"),
            new OA\Response(response: 409, description: "Conflict - Duplicate Entry"), // New response for duplicate
            new OA\Response(response: 500, description: "Internal Server Error")
        ]
    )]
    public function update(int $id): void
    {
        try {
            $data = $this->request->all(); // Use request data for update
            $airport = $this->airportService->updateAirport($id, $data);
            $this->response->success('Airport updated successfully', $airport->toArray());
        } catch (ValidationException $e) {
            $this->response->error($e->getMessage(), 422, $e->getErrors());
        } catch (NotFoundException $e) { // Catch Not Found specifically
            $this->response->error($e->getMessage(), 404);
        } catch (DuplicateEntryException $e) { // Catch duplicate entry specifically
            $this->response->error($e->getMessage(), 409); // 409 Conflict for duplicates
        } catch (\Throwable $e) {
            $this->response->error('Failed to update airport: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete an airport.
     * DELETE /api/v1/airports/{id}
     * @param int $id
     */
    #[OA\Delete(
        path: "/airports/{id}",
        summary: "Delete an airport by ID",
        tags: ["Airports"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID of the airport to delete",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Airport deleted successfully",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Airport deleted successfully")
                    ]
                )
            ),
            //new OA\Response(response: 204, description: "Airport deleted successfully"),
            new OA\Response(response: 404, description: "Airport not found"),
            new OA\Response(response: 500, description: "Internal Server Error")
        ]
    )]
    public function destroy(int $id): void
    {
        try {
            $this->airportService->deleteAirport($id);
            $this->response->success('Airport deleted successfully', ['success' => true], 200);
            // If you prefer 204 No Content and no body:
            // $this->response->success('', [], 204);
        } catch (NotFoundException $e) { // Catch Not Found specifically
            $this->response->error($e->getMessage(), 404);
        } catch (\Throwable $e) {
            $this->response->error('Failed to delete airport: ' . $e->getMessage(), 500);
        }
    }
}