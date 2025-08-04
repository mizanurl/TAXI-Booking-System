<?php

namespace App\Database\Seeders;

use PDO;
use App\Models\Car;

class CarSeeder
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function run(): void
    {
        // Check if table is empty to prevent duplicate seeding
        $stmt = $this->db->query("SELECT COUNT(*) FROM cars");
        if ($stmt->fetchColumn() > 0) {
            echo "Cars table is not empty. Skipping CarSeeder.\n";
            return;
        }

        // Define raw data for cars
        $rawCarsData = [
            [
                'regular_name' => '2 Passenger  Luxury Minivan',
                'short_name' => '2pv Luxury Vehicle',
                'color' => 'Silver',
                'car_photo' => '1-4961.webp',
                'car_features' => 'Boston Airport Taxi haveself modified sedans for great luxury experience. A Sedan car is recognized by its fixed B â€“pillar between front and rear windows. Our luxury Sedans has aerodynamic design which makes them more comfortable and safe at high speeds. With beige interiors and leather upholstery, the interiors are very comfortable and friendly to use.',
                'base_fare' => 25.00,
                'minimum_fare' => 60.00,
                'small_luggage_capacity' => 1,
                'large_luggage_capacity' => 2,
                'extra_luggage_capacity' => 8,
                'num_of_passengers' => 2,
                'is_child_seat' => 1,
                'status' => 1
            ],
            [
                'regular_name' => '3 Passenger Luxury Vehicle ',
                'short_name' => '3pv Luxury Vehicle',
                'color' => 'White',
                'car_photo' => '2-7274.webp',
                'car_features' => 'For family outings or friends get together we have our luxury vans. They are more spacious and comfortable than any other vehicle for such occasions. They are best for seating capacity of more than 7 people. With integrated audio and video panels for great entertainment on the drive book our luxury vans.',
                'base_fare' => 30.00,
                'minimum_fare' => 90.00,
                'small_luggage_capacity' => 1,
                'large_luggage_capacity' => 3,
                'extra_luggage_capacity' => 7,
                'num_of_passengers' => 3,
                'is_child_seat' => 1,
                'status' => 1
            ],
            [
                'regular_name' => '4 Passenger Luxury Vehicle',
                'short_name' => '4pv Luxury Vehicle',
                'color' => 'Black',
                'car_photo' => '5-8767.webp',
                'car_features' => 'Air Conditioning, GPS, Bluetooth',
                'base_fare' => 35.00,
                'minimum_fare' => 99.00,
                'small_luggage_capacity' => 1,
                'large_luggage_capacity' => 4,
                'extra_luggage_capacity' => 6,
                'num_of_passengers' => 4,
                'is_child_seat' => 1,
                'status' => 1
            ],
            [
                'regular_name' => '5 Passenger Luxury Vehicle',
                'short_name' => '5pv Luxury Vehicle',
                'color' => 'White',
                'car_photo' => '7-5994.webp',
                'car_features' => '<p>mirrored ceilings and stereo, to DVD players, full champagne bars and video game consuls, eight flat screen televisions, chrome wheels and lava lamps.</p>',
                'base_fare' => 40.00,
                'minimum_fare' => 110.00,
                'small_luggage_capacity' => 1,
                'large_luggage_capacity' => 5,
                'extra_luggage_capacity' => 1,
                'num_of_passengers' => 5,
                'is_child_seat' => 1,
                'status' => 1
            ],
            [
                'regular_name' => '6 Passenger Luxury Vehicle',
                'short_name' => '6pv Luxury Vehicle',
                'color' => 'White',
                'car_photo' => '21-9230.jpg',
                'car_features' => '<p>mirrored ceilings and stereo, to DVD players, full champagne bars and video game consuls, eight flat screen televisions, chrome wheels and lava lamps.</p>',
                'base_fare' => 45.00,
                'minimum_fare' => 120.00,
                'small_luggage_capacity' => 1,
                'large_luggage_capacity' => 6,
                'extra_luggage_capacity' => 0,
                'num_of_passengers' => 6,
                'is_child_seat' => 1,
                'status' => 1
            ],
            [
                'regular_name' => '7 Passenger Luxury Vehicle',
                'short_name' => '7pv Luxury Vehicle',
                'color' => 'White',
                'car_photo' => '23-1399.webp',
                'car_features' => '<p>mirrored ceilings and stereo, to DVD players, full champagne bars and video game consuls, eight flat screen televisions, chrome wheels and lava lamps.</p>',
                'base_fare' => 50.00,
                'minimum_fare' => 130.00,
                'small_luggage_capacity' => 0,
                'large_luggage_capacity' => 5,
                'extra_luggage_capacity' => 0,
                'num_of_passengers' => 7,
                'is_child_seat' => 0,
                'status' => 1
            ],
        ];

        // Prepare the SQL statement for insertion
        // We include created_at and updated_at as they are part of the model's toArray() output
        $sql = "INSERT INTO cars (regular_name, short_name, color, car_photo, car_features, base_fare, minimum_fare, small_luggage_capacity, large_luggage_capacity, extra_luggage_capacity, num_of_passengers, is_child_seat, status, created_at, updated_at)
                VALUES (:regular_name, :short_name, :color, :car_photo, :car_features, :base_fare, :minimum_fare, :small_luggage_capacity, :large_luggage_capacity, :extra_luggage_capacity, :num_of_passengers, :is_child_seat, :status, :created_at, :updated_at)";
        $stmt = $this->db->prepare($sql);

        $seededCount = 0;
        foreach ($rawCarsData as $carData) {
            // Create a Car model instance
            // We pass null for id, and let the model handle created_at/updated_at defaults
            $car = new Car(
                id: null,
                regularName: $carData['regular_name'],
                shortName: $carData['short_name'],
                color: $carData['color'],
                carPhoto: $carData['car_photo'],
                carFeatures: $carData['car_features'],
                baseFare: $carData['base_fare'],
                minimumFare: $carData['minimum_fare'],
                smallLuggageCapacity: $carData['small_luggage_capacity'],
                largeLuggageCapacity: $carData['large_luggage_capacity'] ?? null,
                extraLuggageCapacity: $carData['extra_luggage_capacity'] ?? null,
                numOfPassengers: $carData['num_of_passengers'],
                isChildSeat: $carData['is_child_seat'],
                status: $carData['status'],
            );

            // Get the data in array format suitable for database insertion
            $dataToInsert = $car->toArray();

            // Remove 'id' as it's auto-incremented
            unset($dataToInsert['id']);

            // Execute the statement
            $stmt->execute($dataToInsert);
            $seededCount++;
        }
        echo "Seeded " . $seededCount . " cars.\n";
    }
}