<?php

namespace App\Database\Seeders;

use PDO;
use App\Models\CarSlabFare;

class CarSlabFareSeeder
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function run(): void
    {
        // Check if table is empty to prevent duplicate seeding
        $stmt = $this->db->query("SELECT COUNT(*) FROM car_slab_fares");
        if ($stmt->fetchColumn() > 0) {
            echo "car_slab_fares table is not empty. Skipping CarSlabFare.\n";
            return;
        }

        // Define raw data for car_slab_fares
        $rawCarSlabFaresData = [
            [
                'car_id' => 1, // Assuming car_id 1 corresponds to '2 Passenger Luxury Minivan'
                'slab_id' => 1,
                'fare_amount' => 5.00, // Fare amount below 6 miles
                'status' => 1
            ],
            [
                'car_id' => 1,
                'slab_id' => 1,
                'fare_amount' => 5.00, // Fare amount from 6 miles & below 9 miles
                'status' => 1
            ],
            [
                'car_id' => 1,
                'slab_id' => 2,
                'fare_amount' => 4.00, // Fare amount from 9 miles & below 11 miles
                'status' => 1
            ],
            [
                'car_id' => 1,
                'slab_id' => 3,
                'fare_amount' => 4.00, // Fare amount from 11 miles & below 19 miles
                'status' => 1
            ],
            [
                'car_id' => 1,
                'slab_id' => 4,
                'fare_amount' => 4.00, // Fare amount from 19 miles & below 24 miles
                'status' => 1
            ],
            [
                'car_id' => 1,
                'slab_id' => 5,
                'fare_amount' => 3.50, // Fare amount from 24 miles & below 100 miles
                'status' => 1
            ],
            [
                'car_id' => 1,
                'slab_id' => 6,
                'fare_amount' => 3.50, // Fare amount from & above 100 miles
                'status' => 1
            ],
            [
                'car_id' => 1,
                'slab_id' => 7,
                'fare_amount' => 50.00, // Fare amount below 2 hours
                'status' => 1
            ],
            [
                'car_id' => 1,
                'slab_id' => 7,
                'fare_amount' => 50.00, // Fare amount from 2 hours & below 5 hours
                'status' => 1
            ],
            [
                'car_id' => 1,
                'slab_id' => 8,
                'fare_amount' => 50.00, // Fare amount from 5 hours & below 10 hours
                'status' => 1
            ],
            [
                'car_id' => 1,
                'slab_id' => 9,
                'fare_amount' => 50.00, // Fare amount from 10 hours & below 15 hours
                'status' => 1
            ],
            [
                'car_id' => 1,
                'slab_id' => 10,
                'fare_amount' => 50.00, // Fare amount from 15 hours & below 20 hours
                'status' => 1
            ],
            [
                'car_id' => 1,
                'slab_id' => 11,
                'fare_amount' => 50.00, // Fare amount from 20 hours & below 24 hours
                'status' => 1
            ],
            [
                'car_id' => 1,
                'slab_id' => 12,
                'fare_amount' => 50.00, // Fare amount from & above 24 hours
                'status' => 1
            ],
            [
                'car_id' => 2, // Assuming car_id 2 corresponds to '3 Passenger Luxury Vehicle'
                'slab_id' => 1,
                'fare_amount' => 5.00, // Fare amount below 6 miles
                'status' => 1
            ],
            [
                'car_id' => 2,
                'slab_id' => 1,
                'fare_amount' => 5.00, // Fare amount from 6 miles & below 9 miles
                'status' => 1
            ],
            [
                'car_id' => 2,
                'slab_id' => 2,
                'fare_amount' => 5.00, // Fare amount from 9 miles & below 11 miles
                'status' => 1
            ],
            [
                'car_id' => 2,
                'slab_id' => 3,
                'fare_amount' => 4.00, // Fare amount from 11 miles & below 19 miles
                'status' => 1
            ],
            [
                'car_id' => 2,
                'slab_id' => 4,
                'fare_amount' => 3.50, // Fare amount from 19 miles & below 24 miles
                'status' => 1
            ],
            [
                'car_id' => 2,
                'slab_id' => 5,
                'fare_amount' => 3.50, // Fare amount from 24 miles & below 100 miles
                'status' => 1
            ],
            [
                'car_id' => 2,
                'slab_id' => 6,
                'fare_amount' => 3.50, // Fare amount from & above 100 miles
                'status' => 1
            ],
            [
                'car_id' => 2,
                'slab_id' => 7,
                'fare_amount' => 50.00, // Fare amount below 2 hours
                'status' => 1
            ],
            [
                'car_id' => 2,
                'slab_id' => 7,
                'fare_amount' => 50.00, // Fare amount from 2 hours & below 5 hours
                'status' => 1
            ],
            [
                'car_id' => 2,
                'slab_id' => 8,
                'fare_amount' => 50.00, // Fare amount from 5 hours & below 10 hours
                'status' => 1
            ],
            [
                'car_id' => 2,
                'slab_id' => 9,
                'fare_amount' => 50.00, // Fare amount from 10 hours & below 15 hours
                'status' => 1
            ],
            [
                'car_id' => 2,
                'slab_id' => 10,
                'fare_amount' => 50.00, // Fare amount from 15 hours & below 20 hours
                'status' => 1
            ],
            [
                'car_id' => 2,
                'slab_id' => 11,
                'fare_amount' => 50.00, // Fare amount from 20 hours & below 24 hours
                'status' => 1
            ],
            [
                'car_id' => 2,
                'slab_id' => 12,
                'fare_amount' => 50.00, // Fare amount from & above 24 hours
                'status' => 1
            ],
            [
                'car_id' => 3, // Assuming car_id 3 corresponds to '4 Passenger Luxury Vehicle'
                'slab_id' => 1,
                'fare_amount' => 5.00, // Fare amount below 6 miles
                'status' => 1
            ],
            [
                'car_id' => 3,
                'slab_id' => 1,
                'fare_amount' => 5.00, // Fare amount from 6 miles & below 9 miles
                'status' => 1
            ],
            [
                'car_id' => 3,
                'slab_id' => 2,
                'fare_amount' => 5.00, // Fare amount from 9 miles & below 11 miles
                'status' => 1
            ],
            [
                'car_id' => 3,
                'slab_id' => 3,
                'fare_amount' => 4.50, // Fare amount from 11 miles & below 19 miles
                'status' => 1
            ],
            [
                'car_id' => 3,
                'slab_id' => 4,
                'fare_amount' => 4.00, // Fare amount from 19 miles & below 24 miles
                'status' => 1
            ],
            [
                'car_id' => 3,
                'slab_id' => 5,
                'fare_amount' => 3.50, // Fare amount from 24 miles & below 100 miles
                'status' => 1
            ],
            [
                'car_id' => 3,
                'slab_id' => 6,
                'fare_amount' => 3.50, // Fare amount from & above 100 miles
                'status' => 1
            ],
            [
                'car_id' => 3,
                'slab_id' => 7,
                'fare_amount' => 60.00, // Fare amount below 2 hours
                'status' => 1
            ],
            [
                'car_id' => 3,
                'slab_id' => 7,
                'fare_amount' => 60.00, // Fare amount from 2 hours & below 5 hours
                'status' => 1
            ],
            [
                'car_id' => 3,
                'slab_id' => 8,
                'fare_amount' => 60.00, // Fare amount from 5 hours & below 10 hours
                'status' => 1
            ],
            [
                'car_id' => 3,
                'slab_id' => 9,
                'fare_amount' => 60.00, // Fare amount from 10 hours & below 15 hours
                'status' => 1
            ],
            [
                'car_id' => 3,
                'slab_id' => 10,
                'fare_amount' => 60.00, // Fare amount from 15 hours & below 20 hours
                'status' => 1
            ],
            [
                'car_id' => 3,
                'slab_id' => 11,
                'fare_amount' => 60.00, // Fare amount from 20 hours & below 24 hours
                'status' => 1
            ],
            [
                'car_id' => 3,
                'slab_id' => 12,
                'fare_amount' => 60.00, // Fare amount from & above 24 hours
                'status' => 1
            ],
            [
                'car_id' => 4, // Assuming car_id 4 corresponds to '5 Passenger Luxury Vehicle'
                'slab_id' => 1,
                'fare_amount' => 5.00, // Fare amount below 6 miles
                'status' => 1
            ],
            [
                'car_id' => 4,
                'slab_id' => 1,
                'fare_amount' => 5.00, // Fare amount from 6 miles & below 9 miles
                'status' => 1
            ],
            [
                'car_id' => 4,
                'slab_id' => 2,
                'fare_amount' => 5.00, // Fare amount from 9 miles & below 11 miles
                'status' => 1
            ],
            [
                'car_id' => 4,
                'slab_id' => 3,
                'fare_amount' => 5.00, // Fare amount from 11 miles & below 19 miles
                'status' => 1
            ],
            [
                'car_id' => 4,
                'slab_id' => 4,
                'fare_amount' => 4.00, // Fare amount from 19 miles & below 24 miles
                'status' => 1
            ],
            [
                'car_id' => 4,
                'slab_id' => 5,
                'fare_amount' => 3.75, // Fare amount from 24 miles & below 100 miles
                'status' => 1
            ],
            [
                'car_id' => 4,
                'slab_id' => 6,
                'fare_amount' => 3.50, // Fare amount from & above 100 miles
                'status' => 1
            ],
            [
                'car_id' => 4,
                'slab_id' => 7,
                'fare_amount' => 70.00, // Fare amount below 2 hours
                'status' => 1
            ],
            [
                'car_id' => 4,
                'slab_id' => 7,
                'fare_amount' => 70.00, // Fare amount from 2 hours & below 5 hours
                'status' => 1
            ],
            [
                'car_id' => 4,
                'slab_id' => 8,
                'fare_amount' => 70.00, // Fare amount from 5 hours & below 10 hours
                'status' => 1
            ],
            [
                'car_id' => 4,
                'slab_id' => 9,
                'fare_amount' => 70.00, // Fare amount from 10 hours & below 15 hours
                'status' => 1
            ],
            [
                'car_id' => 4,
                'slab_id' => 10,
                'fare_amount' => 70.00, // Fare amount from 15 hours & below 20 hours
                'status' => 1
            ],
            [
                'car_id' => 4,
                'slab_id' => 11,
                'fare_amount' => 70.00, // Fare amount from 20 hours & below 24 hours
                'status' => 1
            ],
            [
                'car_id' => 4,
                'slab_id' => 12,
                'fare_amount' => 70.00, // Fare amount from & above 24 hours
                'status' => 1
            ],
            [
                'car_id' => 5, // Assuming car_id 5 corresponds to '6 Passenger Luxury Vehicle'
                'slab_id' => 1,
                'fare_amount' => 6.00, // Fare amount below 6 miles
                'status' => 1
            ],
            [
                'car_id' => 5,
                'slab_id' => 1,
                'fare_amount' => 5.50, // Fare amount from 6 miles & below 9 miles
                'status' => 1
            ],
            [
                'car_id' => 5,
                'slab_id' => 2,
                'fare_amount' => 5.00, // Fare amount from 9 miles & below 11 miles
                'status' => 1
            ],
            [
                'car_id' => 5,
                'slab_id' => 3,
                'fare_amount' => 5.00, // Fare amount from 11 miles & below 19 miles
                'status' => 1
            ],
            [
                'car_id' => 5,
                'slab_id' => 4,
                'fare_amount' => 4.50, // Fare amount from 19 miles & below 24 miles
                'status' => 1
            ],
            [
                'car_id' => 5,
                'slab_id' => 5,
                'fare_amount' => 4.00, // Fare amount from 24 miles & below 100 miles
                'status' => 1
            ],
            [
                'car_id' => 5,
                'slab_id' => 6,
                'fare_amount' => 4.00, // Fare amount from & above 100 miles
                'status' => 1
            ],
            [
                'car_id' => 5,
                'slab_id' => 7,
                'fare_amount' => 75.00, // Fare amount below 2 hours
                'status' => 1
            ],
            [
                'car_id' => 5,
                'slab_id' => 7,
                'fare_amount' => 75.00, // Fare amount from 2 hours & below 5 hours
                'status' => 1
            ],
            [
                'car_id' => 5,
                'slab_id' => 8,
                'fare_amount' => 75.00, // Fare amount from 5 hours & below 10 hours
                'status' => 1
            ],
            [
                'car_id' => 5,
                'slab_id' => 9,
                'fare_amount' => 75.00, // Fare amount from 10 hours & below 15 hours
                'status' => 1
            ],
            [
                'car_id' => 5,
                'slab_id' => 10,
                'fare_amount' => 75.00, // Fare amount from 15 hours & below 20 hours
                'status' => 1
            ],
            [
                'car_id' => 5,
                'slab_id' => 11,
                'fare_amount' => 75.00, // Fare amount from 20 hours & below 24 hours
                'status' => 1
            ],
            [
                'car_id' => 5,
                'slab_id' => 12,
                'fare_amount' => 75.00, // Fare amount from & above 24 hours
                'status' => 1
            ],
            [
                'car_id' => 6, // Assuming car_id 6 corresponds to '7 Passenger Luxury Vehicle'
                'slab_id' => 1,
                'fare_amount' => 6.00, // Fare amount below 6 miles
                'status' => 1
            ],
            [
                'car_id' => 6,
                'slab_id' => 1,
                'fare_amount' => 6.00, // Fare amount from 6 miles & below 9 miles
                'status' => 1
            ],
            [
                'car_id' => 6,
                'slab_id' => 2,
                'fare_amount' => 5.50, // Fare amount from 9 miles & below 11 miles
                'status' => 1
            ],
            [
                'car_id' => 6,
                'slab_id' => 3,
                'fare_amount' => 5.00, // Fare amount from 11 miles & below 19 miles
                'status' => 1
            ],
            [
                'car_id' => 6,
                'slab_id' => 4,
                'fare_amount' => 5.00, // Fare amount from 19 miles & below 24 miles
                'status' => 1
            ],
            [
                'car_id' => 6,
                'slab_id' => 5,
                'fare_amount' => 4.50, // Fare amount from 24 miles & below 100 miles
                'status' => 1
            ],
            [
                'car_id' => 6,
                'slab_id' => 6,
                'fare_amount' => 4.00, // Fare amount from & above 100 miles
                'status' => 1
            ],
            [
                'car_id' => 6,
                'slab_id' => 7,
                'fare_amount' => 70.00, // Fare amount below 2 hours
                'status' => 1
            ],
            [
                'car_id' => 6,
                'slab_id' => 7,
                'fare_amount' => 70.00, // Fare amount from 2 hours & below 5 hours
                'status' => 1
            ],
            [
                'car_id' => 6,
                'slab_id' => 8,
                'fare_amount' => 70.00, // Fare amount from 5 hours & below 10 hours
                'status' => 1
            ],
            [
                'car_id' => 6,
                'slab_id' => 9,
                'fare_amount' => 70.00, // Fare amount from 10 hours & below 15 hours
                'status' => 1
            ],
            [
                'car_id' => 6,
                'slab_id' => 10,
                'fare_amount' => 70.00, // Fare amount from 15 hours & below 20 hours
                'status' => 1
            ],
            [
                'car_id' => 6,
                'slab_id' => 11,
                'fare_amount' => 70.00, // Fare amount from 20 hours & below 24 hours
                'status' => 1
            ],
            [
                'car_id' => 6,
                'slab_id' => 12,
                'fare_amount' => 70.00, // Fare amount from & above 24 hours
                'status' => 1
            ],
        ];

        // Prepare the SQL statement for insertion
        // We include created_at and updated_at as they are part of the model's toArray() output
        $sql = "INSERT INTO car_slab_fares (car_id, slab_id, fare_amount, status, created_at, updated_at)
                VALUES (:car_id, :slab_id, :fare_amount, :status, :created_at, :updated_at)";
        $stmt = $this->db->prepare($sql);

        $seededCount = 0;
        foreach ($rawCarSlabFaresData as $carSlabFareData) {
            // Create a CarSlabFare model instance
            // We pass null for id, and let the model handle created_at/updated_at defaults
            $carSlabFare = new CarSlabFare(
                id: null,
                carId: $carSlabFareData['car_id'],
                slabId: $carSlabFareData['slab_id'],
                fareAmount: $carSlabFareData['fare_amount'],
                status: $carSlabFareData['status'],
            );

            // Get the data in array format suitable for database insertion
            $dataToInsert = $carSlabFare->toArray();

            // Remove 'id' as it's auto-incremented
            unset($dataToInsert['id']);

            // Execute the statement
            $stmt->execute($dataToInsert);
            $seededCount++;
        }
        echo "Seeded " . $seededCount . " car_slab_fares.\n";
    }
}