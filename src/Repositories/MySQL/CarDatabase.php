<?php

namespace App\Repositories\MySQL;

use App\Models\Car;
use App\Repositories\Contracts\CarInterface;
use PDO;
use PDOException;

class CarDatabase implements CarInterface
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Find a car by its ID.
     * @param int $id
     * @return Car|null
     * @throws PDOException
     */
    public function findById(int $id): ?Car
    {
        $stmt = $this->db->prepare("SELECT * FROM cars WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $carData = $stmt->fetch(PDO::FETCH_ASSOC);

        return $carData ? Car::fromArray($carData) : null;
    }

    /**
     * Find a suitable car by the number of passengers and luggages.
     * @param int $passengers
     * @param int $luggages
     * @param int $isChildSeat
     * @return Car|null
     * @throws PDOException
     */
    public function findSuitableCar(int $passengers, int $luggages, int $isChildSeat): ?Car
    {
        $query = "SELECT id FROM cars 
                    WHERE num_of_passengers >= :passengers 
                    AND (small_luggage_capacity + large_luggage_capacity) >= :luggages 
                    AND is_child_seat = :isChildSeat 
                    AND status = 1 
                    LIMIT 1";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':passengers', $passengers, PDO::PARAM_INT);
        $stmt->bindParam(':luggages', $luggages, PDO::PARAM_INT);
        $stmt->bindParam(':isChildSeat', $isChildSeat, PDO::PARAM_INT);
        $stmt->execute();
        $carData = $stmt->fetch(PDO::FETCH_ASSOC);

        return $carData ? Car::fromArray($carData) : null;
    }

    /**
     * Get all cars.
     * @return Car[]
     * @throws PDOException
     */
    public function all(): array
    {
        $stmt = $this->db->query("SELECT * FROM cars");
        $carsData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $cars = [];
        foreach ($carsData as $carData) {
            $cars[] = Car::fromArray($carData);
        }
        return $cars;
    }

    /**
     * Create a new car.
     * @param Car $car
     * @return int The ID of the newly created car.
     * @throws PDOException
     */
    public function create(Car $car): int
    {
        $data = $car->toArray();

        $sql = "INSERT INTO cars (regular_name, short_name, color, car_photo, car_features, base_fare, minimum_fare, small_luggage_capacity, large_luggage_capacity, extra_luggage_capacity, num_of_passengers, is_child_seat, status, created_at, updated_at)
                VALUES (:regular_name, :short_name, :color, :car_photo, :car_features, :base_fare, :minimum_fare, :small_luggage_capacity, :large_luggage_capacity, :extra_luggage_capacity, :num_of_passengers, :is_child_seat, :status, :created_at, :updated_at)";
        $stmt = $this->db->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':regular_name', $data['regular_name']);
        $stmt->bindParam(':short_name', $data['short_name']);
        $stmt->bindParam(':color', $data['color']);
        $stmt->bindParam(':car_photo', $data['car_photo']);
        $stmt->bindParam(':car_features', $data['car_features']);
        $stmt->bindParam(':base_fare', $data['base_fare']);
        $stmt->bindParam(':minimum_fare', $data['minimum_fare']);
        $stmt->bindParam(':small_luggage_capacity', $data['small_luggage_capacity'], PDO::PARAM_INT);
        $stmt->bindParam(':large_luggage_capacity', $data['large_luggage_capacity'], PDO::PARAM_INT);
        $stmt->bindParam(':extra_luggage_capacity', $data['extra_luggage_capacity'], PDO::PARAM_INT);
        $stmt->bindParam(':num_of_passengers', $data['num_of_passengers'], PDO::PARAM_INT);
        $stmt->bindParam(':is_child_seat', $data['is_child_seat'], PDO::PARAM_INT);
        $stmt->bindParam(':status', $data['status'], PDO::PARAM_INT);
        $stmt->bindParam(':created_at', $data['created_at']);
        $stmt->bindParam(':updated_at', $data['updated_at']);

        $stmt->execute();
        return (int)$this->db->lastInsertId();
    }

    /**
     * Update an existing car.
     * @param Car $car
     * @return bool True on success, false otherwise.
     * @throws PDOException
     */
    public function update(Car $car): bool
    {
        $data = $car->toArray();

        $sql = "UPDATE cars SET
                    regular_name = :regular_name,
                    short_name = :short_name,
                    color = :color,
                    car_photo = :car_photo,
                    car_features = :car_features,
                    base_fare = :base_fare,
                    minimum_fare = :minimum_fare,
                    small_luggage_capacity = :small_luggage_capacity,
                    large_luggage_capacity = :large_luggage_capacity,
                    extra_luggage_capacity = :extra_luggage_capacity,
                    num_of_passengers = :num_of_passengers,
                    is_child_seat = :is_child_seat,
                    status = :status,
                    updated_at = :updated_at
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':regular_name', $data['regular_name']);
        $stmt->bindParam(':short_name', $data['short_name']);
        $stmt->bindParam(':color', $data['color']);
        $stmt->bindParam(':car_photo', $data['car_photo']);
        $stmt->bindParam(':car_features', $data['car_features']);
        $stmt->bindParam(':base_fare', $data['base_fare']);
        $stmt->bindParam(':minimum_fare', $data['minimum_fare']);
        $stmt->bindParam(':small_luggage_capacity', $data['small_luggage_capacity'], PDO::PARAM_INT);
        $stmt->bindParam(':large_luggage_capacity', $data['large_luggage_capacity'], PDO::PARAM_INT);
        $stmt->bindParam(':extra_luggage_capacity', $data['extra_luggage_capacity'], PDO::PARAM_INT);
        $stmt->bindParam(':num_of_passengers', $data['num_of_passengers'], PDO::PARAM_INT);
        $stmt->bindParam(':is_child_seat', $data['is_child_seat'], PDO::PARAM_INT);
        $stmt->bindParam(':status', $data['status'], PDO::PARAM_INT);
        $stmt->bindParam(':updated_at', $data['updated_at']);
        $stmt->bindParam(':id', $data['id'], PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Delete a car by its ID.
     * @param int $id
     * @return bool True on success, false otherwise.
     * @throws PDOException
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM cars WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}