<?php

namespace App\Services;

use App\Models\Car;
use App\Models\CarSlabFare;
use App\Repositories\Contracts\CarInterface;
use App\Repositories\Contracts\CarSlabFareInterface;
use App\Exceptions\NotFoundException;
use PDOException;
use App\Traits\FileUploadTrait;

class CarService
{
    use FileUploadTrait;

    private CarInterface $carRepository;
    private CarSlabFareInterface $carSlabFareRepository;

    // Define constants for car photo upload
    private const CAR_PHOTO_UPLOAD_DIR_RELATIVE = 'uploads/cars/'; // Relative path from public/
    private const CAR_PHOTO_ALLOWED_MIMES = ['image/jpeg', 'image/gif', 'image/png', 'image/webp'];
    private const CAR_PHOTO_MAX_SIZE_BYTES = 100 * 1024; // 100 KB in bytes
    private const CAR_PHOTO_MAX_WIDTH = 300;
    private const CAR_PHOTO_MAX_HEIGHT = 200;

    public function __construct(CarInterface $carRepository, CarSlabFareInterface $carSlabFareRepository)
    {
        $this->carRepository = $carRepository;
        $this->carSlabFareRepository = $carSlabFareRepository;
    }

    /**
     * Get a single car by ID.
     * @param int $id
     * @return Car
     * @throws NotFoundException If the car is not found.
     * @throws \Exception On database errors.
     */
    public function getCar(int $id): Car
    {
        try {
            $car = $this->carRepository->findById($id);
            if (!$car) {
                throw new NotFoundException("Car with ID {$id} not found.");
            }
            return $car;
        } catch (PDOException $e) {
            error_log("Database error in getCar: " . $e->getMessage());
            throw new \Exception("Failed to retrieve car due to a database error.");
        } catch (\Exception $e) {
            error_log("Error in getCar: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get a single car by ID including its associated slab fares.
     * @param int $id
     * @return array An associative array containing the Car object and its slabs.
     * @throws NotFoundException If the car is not found.
     * @throws \Exception On database errors.
     */
    public function getCarWithSlabs(int $id): array
    {
        try {
            $car = $this->carRepository->findById($id);
            if (!$car) {
                throw new NotFoundException("Car with ID {$id} not found.");
            }

            $slabs = $this->carSlabFareRepository->getByCarId($id);
            $slabsArray = array_map(fn($slabFare) => $slabFare->toArray(), $slabs);

            return [
                'car' => $car->toArray(),
                'slabs' => $slabsArray
            ];
        } catch (PDOException $e) {
            error_log("Database error in getCarWithSlabs: " . $e->getMessage());
            throw new \Exception("Failed to retrieve car and its slabs due to a database error.");
        } catch (\Exception $e) {
            error_log("Error in getCarWithSlabs: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get all cars.
     * @return Car[]
     * @throws \Exception On database errors.
     */
    public function getAllCars(): array
    {
        try {
            return $this->carRepository->all();
        } catch (PDOException $e) {
            error_log("Database error in getAllCars: " . $e->getMessage());
            throw new \Exception("Failed to retrieve cars due to a database error.");
        } catch (\Exception $e) {
            error_log("Error in getAllCars: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a new car.
     *
     * @param array $data Validated data for the new car.
     * @return Car
     * @throws \Exception On creation failure or file upload/validation fails.
     */
    public function createCar(array $data): Car
    {
        $carPhotoFilenameForDb = null;

        if (isset($data['car_photo']) && is_array($data['car_photo']) && $data['car_photo']['error'] === UPLOAD_ERR_OK) {
            $fileData = $data['car_photo'];

            list($width, $height) = getimagesize($fileData['tmp_name']);
            if ($width > self::CAR_PHOTO_MAX_WIDTH || $height > self::CAR_PHOTO_MAX_HEIGHT) {
                throw new \InvalidArgumentException("Car photo dimensions exceed maximum allowed: " . self::CAR_PHOTO_MAX_WIDTH . "x" . self::CAR_PHOTO_MAX_HEIGHT . " pixels.");
            }

            $carPhotoFilenameForDb = $this->uploadUniqueNameFile(
                $fileData,
                self::CAR_PHOTO_UPLOAD_DIR_RELATIVE,
                self::CAR_PHOTO_ALLOWED_MIMES,
                self::CAR_PHOTO_MAX_SIZE_BYTES
            );

            if (!$carPhotoFilenameForDb) {
                throw new \Exception("Failed to upload car photo.");
            }
        } else {
            if (!isset($data['car_photo']) || (isset($data['car_photo']) && $data['car_photo']['error'] !== UPLOAD_ERR_OK)) {
                throw new \Exception("Car photo is required and must be a valid uploaded file for creation.");
            }
        }

        try {
            $car = new Car(
                id: null,
                regularName: $data['regular_name'],
                shortName: $data['short_name'] ?? null,
                color: $data['color'],
                carPhoto: $carPhotoFilenameForDb,
                carFeatures: $data['car_features'],
                baseFare: (float) $data['base_fare'],
                minimumFare: (float) $data['minimum_fare'],
                smallLuggageCapacity: (int) $data['small_luggage_capacity'],
                largeLuggageCapacity: (int) $data['large_luggage_capacity'],
                extraLuggageCapacity: (int) $data['extra_luggage_capacity'],
                numOfPassengers: (int) $data['num_of_passengers'],
                isChildSeat: (int) $data['is_child_seat'],
                status: (int) $data['status']
            );

            $newId = $this->carRepository->create($car);
            if (!$newId) {
                if ($carPhotoFilenameForDb) {
                    $this->deleteFile(self::CAR_PHOTO_UPLOAD_DIR_RELATIVE . $carPhotoFilenameForDb);
                }
                throw new \Exception("Failed to create car in the database.");
            }
            $car->id = $newId;
            return $car;
        } catch (PDOException $e) {
            if ($carPhotoFilenameForDb) {
                $this->deleteFile(self::CAR_PHOTO_UPLOAD_DIR_RELATIVE . $carPhotoFilenameForDb);
            }
            error_log("Database error in createCar: " . $e->getMessage());
            throw new \Exception("Failed to create car due to a database error.");
        } catch (\Exception $e) {
            if ($carPhotoFilenameForDb) {
                $this->deleteFile(self::CAR_PHOTO_UPLOAD_DIR_RELATIVE . $carPhotoFilenameForDb);
            }
            error_log("Error in createCar: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update an existing car.
     *
     * @param int $id The ID of the car to update.
     * @param array $data Validated data for updating the car.
     * @return Car
     * @throws NotFoundException If the car is not found.
     * @throws \Exception On update failure or file upload/validation fails.
     */
    public function updateCar(int $id, array $data): Car
    {
        try {
            $car = $this->carRepository->findById($id);
            if (!$car) {
                throw new NotFoundException("Car with ID {$id} not found.");
            }

            $currentCarPhotoFilenameInDb = $car->carPhoto;
            $carPhotoFilenameForDb = $currentCarPhotoFilenameInDb;

            if (isset($data['car_photo']) && is_array($data['car_photo']) && $data['car_photo']['error'] === UPLOAD_ERR_OK) {
                $fileData = $data['car_photo'];

                list($width, $height) = getimagesize($fileData['tmp_name']);
                if ($width > self::CAR_PHOTO_MAX_WIDTH || $height > self::CAR_PHOTO_MAX_HEIGHT) {
                    throw new \InvalidArgumentException("Car photo dimensions exceed maximum allowed: " . self::CAR_PHOTO_MAX_WIDTH . "x" . self::CAR_PHOTO_MAX_HEIGHT . " pixels.");
                }

                $uploadedFilename = $this->uploadUniqueNameFile(
                    $fileData,
                    self::CAR_PHOTO_UPLOAD_DIR_RELATIVE,
                    self::CAR_PHOTO_ALLOWED_MIMES,
                    self::CAR_PHOTO_MAX_SIZE_BYTES
                );

                if (!$uploadedFilename) {
                    throw new \Exception("Failed to upload new car photo.");
                }
                $carPhotoFilenameForDb = $uploadedFilename;

                if ($currentCarPhotoFilenameInDb) {
                    $this->deleteFile(self::CAR_PHOTO_UPLOAD_DIR_RELATIVE . $currentCarPhotoFilenameInDb);
                }
            }
            elseif (array_key_exists('car_photo', $data) && $data['car_photo'] === null) {
                if ($currentCarPhotoFilenameInDb) {
                    $this->deleteFile(self::CAR_PHOTO_UPLOAD_DIR_RELATIVE . $currentCarPhotoFilenameInDb);
                }
                $carPhotoFilenameForDb = null;
            }

            $car->regularName = $data['regular_name'] ?? $car->regularName;
            $car->shortName = $data['short_name'] ?? $car->shortName;
            $car->color = $data['color'] ?? $car->color;
            $car->carPhoto = $carPhotoFilenameForDb;
            $car->carFeatures = $data['car_features'] ?? $car->carFeatures;
            $car->baseFare = (float) ($data['base_fare'] ?? $car->baseFare);
            $car->minimumFare = (float) ($data['minimum_fare'] ?? $car->minimumFare);
            $car->smallLuggageCapacity = (int) ($data['small_luggage_capacity'] ?? $car->smallLuggageCapacity);
            $car->largeLuggageCapacity = (int) ($data['large_luggage_capacity'] ?? $car->largeLuggageCapacity);
            $car->extraLuggageCapacity = (int) ($data['extra_luggage_capacity'] ?? $car->extraLuggageCapacity);
            $car->numOfPassengers = (int) ($data['num_of_passengers'] ?? $car->numOfPassengers);
            $car->isChildSeat = (int) ($data['is_child_seat'] ?? $car->isChildSeat);
            $car->status = (int) ($data['status'] ?? $car->status);

            $car->updatedAt = date('Y-m-d H:i:s');

            if (!$this->carRepository->update($car)) {
                throw new \Exception("Failed to update car in the database.");
            }

            return $car;
        } catch (PDOException $e) {
            error_log("Database error in updateCar: " . $e->getMessage());
            throw new \Exception("Failed to update car due to a database error.");
        } catch (\Exception $e) {
            error_log("Error in updateCar: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a car.
     *
     * @param int $id The ID of the car to delete.
     * @return bool True on successful deletion.
     * @throws NotFoundException If the car is not found.
     * @throws \Exception On deletion failure or database errors.
     */
    public function deleteCar(int $id): bool
    {
        try {
            $car = $this->carRepository->findById($id);
            if (!$car) {
                throw new NotFoundException("Car with ID {$id} not found.");
            }

            // Delete associated car_slab_fares first
            // This was already present, confirming it's correct.
            $this->carSlabFareRepository->deleteByCarId($id);

            if (!$this->carRepository->delete($id)) {
                throw new \Exception("Failed to delete car from the database.");
            }

            // Delete the associated car photo file if it exists
            // This was already present, confirming it's correct.
            if ($car->carPhoto) {
                $this->deleteFile(self::CAR_PHOTO_UPLOAD_DIR_RELATIVE . $car->carPhoto);
            }

            return true;
        } catch (PDOException $e) {
            error_log("Database error in deleteCar: " . $e->getMessage());
            throw new \Exception("Failed to delete car due to a database error.");
        } catch (\Exception $e) {
            error_log("Error in deleteCar: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Assigns/updates slab fare data for a specific car.
     * Before adding new data, all existing slab data for the car will be deleted.
     *
     * @param int $carId The ID of the car to assign slabs to.
     * @param array $slabsData An array of slab assignments, each containing 'slab_id', 'fare_amount', 'status'.
     * @return array An array of created CarSlabFare objects.
     * @throws NotFoundException If the car is not found.
     * @throws \Exception On database errors or if slab creation fails.
     */
    public function assignSlabsToCar(int $carId, array $slabsData): array
    {
        try {
            $car = $this->carRepository->findById($carId);
            if (!$car) {
                throw new NotFoundException("Car with ID {$carId} not found. Cannot assign slabs.");
            }

            if (!$this->carSlabFareRepository->deleteByCarId($carId)) {
                error_log("Failed to delete existing car_slab_fares for car ID: {$carId}.");
                throw new \Exception("Failed to clear previous slab assignments for car ID {$carId}.");
            }

            $assignedSlabs = [];
            foreach ($slabsData as $slabEntry) {
                $carSlabFare = new CarSlabFare(
                    id: null,
                    carId: $carId,
                    slabId: (int) $slabEntry['slab_id'],
                    fareAmount: (float) $slabEntry['fare_amount'],
                    status: (int) $slabEntry['status']
                );

                $newId = $this->carSlabFareRepository->create($carSlabFare);
                if (!$newId) {
                    throw new \Exception("Failed to assign slab ID {$slabEntry['slab_id']} to car ID {$carId}.");
                }
                $carSlabFare->id = $newId;
                $assignedSlabs[] = $carSlabFare;
            }

            return $assignedSlabs;
        } catch (PDOException $e) {
            error_log("Database error in assignSlabsToCar: " . $e->getMessage());
            throw new \Exception("Failed to assign slabs to car due to a database error.");
        } catch (\Exception $e) {
            error_log("Error in assignSlabsToCar: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a specific car slab fare entry.
     *
     * @param int $carId The ID of the car.
     * @param int $slabFareId The ID of the car_slab_fare entry to delete.
     * @return bool True on successful deletion.
     * @throws NotFoundException If the car or car slab fare is not found, or if the slab fare does not belong to the car.
     * @throws \Exception On deletion failure or database errors.
     */
    public function deleteCarSlabFare(int $carId, int $slabFareId): bool
    {
        try {
            // Ensure the car exists
            $car = $this->carRepository->findById($carId);
            if (!$car) {
                throw new NotFoundException("Car with ID {$carId} not found.");
            }

            // Find the specific car slab fare entry
            $carSlabFare = $this->carSlabFareRepository->findById($slabFareId);
            if (!$carSlabFare) {
                throw new NotFoundException("Car Slab Fare with ID {$slabFareId} not found.");
            }

            // Ensure the car slab fare belongs to the specified car
            if ($carSlabFare->carId !== $carId) {
                throw new NotFoundException("Car Slab Fare with ID {$slabFareId} does not belong to Car ID {$carId}.");
            }

            if (!$this->carSlabFareRepository->delete($slabFareId)) {
                throw new \Exception("Failed to delete car slab fare with ID {$slabFareId}.");
            }

            return true;
        } catch (PDOException $e) {
            error_log("Database error in deleteCarSlabFare: " . $e->getMessage());
            throw new \Exception("Failed to delete car slab fare due to a database error.");
        } catch (\Exception $e) {
            error_log("Error in deleteCarSlabFare: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update a specific car slab fare entry.
     *
     * @param int $carId The ID of the car.
     * @param int $slabFareId The ID of the car_slab_fare entry to update.
     * @param array $data Validated data for updating the car slab fare (fare_amount, status, slab_id).
     * @return CarSlabFare The updated CarSlabFare object.
     * @throws NotFoundException If the car or car slab fare is not found, or if the slab fare does not belong to the car.
     * @throws \Exception On update failure or database errors.
     */
    public function updateCarSlabFare(int $carId, int $slabFareId, array $data): CarSlabFare
    {
        try {
            // Ensure the car exists
            $car = $this->carRepository->findById($carId);
            if (!$car) {
                throw new NotFoundException("Car with ID {$carId} not found.");
            }

            // Find the specific car slab fare entry
            $carSlabFare = $this->carSlabFareRepository->findById($slabFareId);
            if (!$carSlabFare) {
                throw new NotFoundException("Car Slab Fare with ID {$slabFareId} not found.");
            }

            // Ensure the car slab fare belongs to the specified car
            if ($carSlabFare->carId !== $carId) {
                throw new NotFoundException("Car Slab Fare with ID {$slabFareId} does not belong to Car ID {$carId}.");
            }

            // Update properties from provided data
            if (isset($data['slab_id'])) {
                $carSlabFare->slabId = (int) $data['slab_id'];
            }
            if (isset($data['fare_amount'])) {
                $carSlabFare->fareAmount = (float) $data['fare_amount'];
            }
            if (isset($data['status'])) {
                $carSlabFare->status = (int) $data['status'];
            }
            $carSlabFare->updatedAt = date('Y-m-d H:i:s');

            if (!$this->carSlabFareRepository->update($carSlabFare)) {
                throw new \Exception("Failed to update car slab fare with ID {$slabFareId}.");
            }

            return $carSlabFare;
        } catch (PDOException $e) {
            error_log("Database error in updateCarSlabFare: " . $e->getMessage());
            throw new \Exception("Failed to update car slab fare due to a database error.");
        } catch (\Exception $e) {
            error_log("Error in updateCarSlabFare: " . $e->getMessage());
            throw $e;
        }
    }
}