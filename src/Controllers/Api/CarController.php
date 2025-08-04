<?php

namespace App\Controllers\Api;

use App\Http\Request;
use App\Services\CarService;
use App\Http\Response;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Http\Requests\Api\Car\CreateRequest;
use App\Http\Requests\Api\Car\UpdateRequest;
use App\Http\Requests\Api\Car\AssignSlabsRequest;
use App\Http\Requests\Api\Car\UpdateCarSlabFareRequest;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Car Features", description: "API for managing car vehicles and their pricing slabs.")]
class CarController
{
    private CarService $carService;
    private Request $request;
    private Response $response;

    public function __construct(CarService $carService, Request $request, Response $response)
    {
        $this->carService = $carService;
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @param int $id
     * @return void
     */
    #[OA\Get(
        path: "/cars/{id}",
        summary: "Get a single car by ID including its associated slab fares",
        tags: ["Car Features"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID of the car to retrieve",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", format: "int64", example: 1)
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
                        new OA\Property(property: "message", type: "string", example: "Car and associated slabs retrieved successfully."),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "car", ref: "#/components/schemas/Car"),
                                new OA\Property(
                                    property: "slabs",
                                    type: "array",
                                    items: new OA\Items(ref: "#/components/schemas/CarSlabFare")
                                )
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Car not found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Car with ID 1 not found.")
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Internal Server Error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Failed to retrieve car and its slabs due to a database error.")
                    ]
                )
            )
        ]
    )]
    public function show(int $id): void
    {
        try {
            $data = $this->carService->getCarWithSlabs($id);
            $this->response->success('Car and associated slabs retrieved successfully.', $data, 200);
        } catch (NotFoundException $e) {
            $this->response->error($e->getMessage(), 404);
        } catch (\Exception $e) {
            error_log("CarController error in show: " . $e->getMessage());
            $this->response->error("Failed to retrieve car and its slabs: " . $e->getMessage(), 500);
        }
    }

    /**
     * @return void
     */
    #[OA\Get(
        path: "/cars",
        summary: "Get a list of all cars",
        tags: ["Car Features"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(ref: "#/components/schemas/Car")
                )
            ),
            new OA\Response(
                response: 500,
                description: "Internal Server Error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Failed to retrieve cars due to a database error.")
                    ]
                )
            )
        ]
    )]
    public function index(): void
    {
        try {
            $cars = $this->carService->getAllCars();
            $carsArray = array_map(fn($car) => $car->toArray(), $cars);
            $this->response->success('Cars retrieved successfully', $carsArray, 200);
        } catch (\Exception $e) {
            error_log("CarController error in index: " . $e->getMessage());
            $this->response->error("Failed to retrieve cars: " . $e->getMessage(), 500);
        }
    }

    /**
     * @return void
     */
    #[OA\Post(
        path: "/cars",
        summary: "Create a new car",
        tags: ["Car Features"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(ref: "#/components/schemas/CarCreateRequest")
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Car created successfully",
                content: new OA\JsonContent(ref: "#/components/schemas/Car")
            ),
            new OA\Response(
                response: 422,
                description: "Validation error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "The given data was invalid."),
                        new OA\Property(property: "errors", type: "object", example: ["regular_name" => ["The regular name field is required."]])
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Internal Server Error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Failed to create car due to a database error.")
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

            $car = $this->carService->createCar($validatedData);
            $this->response->success('Car created successfully', $car->toArray(), 201);
        } catch (ValidationException $e) {
            $this->response->error($e->getMessage(), 422, $e->getErrors());
        } catch (\Exception $e) {
            error_log("CarController error in store: " . $e->getMessage());
            $this->response->error("Failed to create car: " . $e->getMessage(), 500);
        }
    }

    /**
     * @param int $id
     * @return void
     */
    #[OA\Put(
        path: "/cars/{id}",
        summary: "Update an existing car",
        tags: ["Car Features"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID of the car to update",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", format: "int64", example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(ref: "#/components/schemas/CarUpdateRequest")
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Car updated successfully",
                content: new OA\JsonContent(ref: "#/components/schemas/Car")
            ),
            new OA\Response(
                response: 404,
                description: "Car not found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Car with ID 1 not found.")
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
                        new OA\Property(property: "errors", type: "object", example: ["color" => ["The color field is required."]])
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Internal Server Error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Failed to update car due to a database error.")
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

            $car = $this->carService->updateCar($id, $validatedData);
            $this->response->success('Car updated successfully', $car->toArray(), 200);
        } catch (NotFoundException $e) {
            $this->response->error($e->getMessage(), 404);
        } catch (ValidationException $e) {
            $this->response->error($e->getMessage(), 422, $e->getErrors());
        } catch (\Exception $e) {
            error_log("CarController error in update: " . $e->getMessage());
            $this->response->error("Failed to update car: " . $e->getMessage(), 500);
        }
    }

    /**
     * @param int $id
     * @return void
     */
    #[OA\Delete(
        path: "/cars/{id}",
        summary: "Delete a car by ID",
        tags: ["Car Features"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID of the car to delete",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", format: "int64", example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Car deleted successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Car deleted successfully.")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Car not found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Car with ID 1 not found.")
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Internal Server Error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Failed to delete car due to a database error.")
                    ]
                )
            )
        ]
    )]
    public function destroy(int $id): void
    {
        try {
            $this->carService->deleteCar($id);
            $this->response->success('Car deleted successfully.', ['success' => true], 200);
        } catch (NotFoundException $e) {
            $this->response->error($e->getMessage(), 404);
        } catch (\Exception $e) {
            error_log("CarController error in destroy: " . $e->getMessage());
            $this->response->error("Failed to delete car: " . $e->getMessage(), 500);
        }
    }

    /**
     * @param int $carId
     * @return void
     */
    #[OA\Post(
        path: "/cars/{carId}/slabs",
        summary: "Assign or update pricing slabs for a specific car",
        description: "This operation will delete all existing slab associations for the car and then insert the new ones provided.",
        tags: ["Car Features"],
        parameters: [
            new OA\Parameter(
                name: "carId",
                description: "ID of the car to assign slabs to",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", format: "int64", example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/CarAssignSlabsRequest")
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Slabs assigned successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Slabs assigned to car successfully."),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(ref: "#/components/schemas/CarSlabFare")
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Car not found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Car with ID 1 not found. Cannot assign slabs.")
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
                        new OA\Property(property: "errors", type: "object", example: ["slabs.0.slab_id" => ["Each slab entry must have a slab ID."]])
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Internal Server Error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Failed to assign slabs to car due to a database error.")
                    ]
                )
            )
        ]
    )]
    public function assignSlabs(int $carId): void
    {
        try {
            $assignSlabsRequest = new AssignSlabsRequest($this->request);
            $validatedData = $assignSlabsRequest->validate();

            $assignedSlabs = $this->carService->assignSlabsToCar($carId, $validatedData['slabs']);
            $this->response->success('Slabs assigned to car successfully.', $assignedSlabs, 200); // $assignedSlabs is already an array of arrays
        } catch (NotFoundException $e) {
            $this->response->error($e->getMessage(), 404);
        } catch (ValidationException $e) {
            $this->response->error($e->getMessage(), 422, $e->getErrors());
        } catch (\Exception $e) {
            error_log("CarController error in assignSlabs: " . $e->getMessage());
            $this->response->error("Failed to assign slabs: " . $e->getMessage(), 500);
        }
    }

    /**
     * Delete a specific car slab fare entry.
     *
     * @param int $carId The ID of the car.
     * @param int $slabFareId The ID of the car_slab_fare entry to delete.
     * @return void
     */
    #[OA\Delete(
        path: "/cars/{carId}/slabs/{slabFareId}",
        summary: "Delete a specific car slab fare entry",
        tags: ["Car Features"],
        parameters: [
            new OA\Parameter(
                name: "carId",
                description: "ID of the car to which the slab fare belongs",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", format: "int64", example: 1)
            ),
            new OA\Parameter(
                name: "slabFareId",
                description: "ID of the car slab fare entry to delete",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", format: "int64", example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Car slab fare deleted successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Car slab fare deleted successfully.")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Not found (car or car slab fare)",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Car with ID 1 not found.")
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Internal Server Error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Failed to delete car slab fare due to a database error.")
                    ]
                )
            )
        ]
    )]
    public function deleteSlabFare(int $carId, int $slabFareId): void
    {
        try {
            $this->carService->deleteCarSlabFare($carId, $slabFareId);
            $this->response->success('Car slab fare deleted successfully.', ['success' => true], 200);
        } catch (NotFoundException $e) {
            $this->response->error($e->getMessage(), 404);
        } catch (\Exception $e) {
            error_log("CarController error in deleteSlabFare: " . $e->getMessage());
            $this->response->error("Failed to delete car slab fare: " . $e->getMessage(), 500);
        }
    }

    /**
     * Update an existing car slab fare entry.
     *
     * @param int $carId The ID of the car.
     * @param int $slabFareId The ID from the 'car_slab_fares' table.
     * @return void
     */
    #[OA\Put(
        path: "/cars/{carId}/slabs/{slabFareId}",
        summary: "Update an existing car slab fare entry",
        tags: ["Car Features"],
        parameters: [
            new OA\Parameter(
                name: "carId",
                description: "ID of the car to which the slab fare belongs",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", format: "int64", example: 1)
            ),
            new OA\Parameter(
                name: "slabFareId",
                description: "ID of the car slab fare entry to update",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", format: "int64", example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/UpdateCarSlabFareRequest")
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Car slab fare updated successfully",
                content: new OA\JsonContent(ref: "#/components/schemas/CarSlabFare")
            ),
            new OA\Response(
                response: 404,
                description: "Not found (car or car slab fare)",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Car with ID 1 not found.")
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
                        new OA\Property(property: "errors", type: "object", example: ["fare_amount" => ["Fare amount must be at least 0."]])
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Internal Server Error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Failed to update car slab fare due to a database error.")
                    ]
                )
            )
        ]
    )]
    public function updateSlabFare(int $carId, int $slabFareId): void
    {
        try {
            $updateRequest = new UpdateCarSlabFareRequest($this->request);
            $validatedData = $updateRequest->validate();

            $updatedSlabFare = $this->carService->updateCarSlabFare($carId, $slabFareId, $validatedData);
            $this->response->success('Car slab fare updated successfully.', $updatedSlabFare->toArray(), 200);
        } catch (NotFoundException $e) {
            $this->response->error($e->getMessage(), 404);
        } catch (ValidationException $e) {
            $this->response->error($e->getMessage(), 422, $e->getErrors());
        } catch (\Exception $e) {
            error_log("CarController error in updateSlabFare: " . $e->getMessage());
            $this->response->error("Failed to update car slab fare: " . $e->getMessage(), 500);
        }
    }
}