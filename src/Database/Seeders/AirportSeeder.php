<?php

namespace App\Database\Seeders;

use PDO;
use App\Models\Airport;

class AirportSeeder
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function run(): void
    {
        // Check if table is empty to prevent duplicate seeding
        $stmt = $this->db->query("SELECT COUNT(*) FROM airports");
        if ($stmt->fetchColumn() > 0) {
            echo "Airports table is not empty. Skipping AirportSeeder.\n";
            return;
        }

        // Define raw data for airports with new fields
        $rawAirportsData = [
            [
                'name' => 'Logan Airport Boston, Ma 02128',
                'description' => 'Logan International Airport, Boston, MA',
                'from_tax_toll' => 7.50,
                'to_tax_toll' => 12.00,
                'status' => 1
            ],
            [
                'name' => 'Hazrat Shahjalal International Airport',
                'description' => 'Main international airport in Bangladesh.',
                'from_tax_toll' => 5.50,
                'to_tax_toll' => 6.00,
                'status' => 1
            ],
            [
                'name' => 'Cox\'s Bazar Airport',
                'description' => 'Domestic airport serving Cox\'s Bazar.',
                'from_tax_toll' => 2.00,
                'to_tax_toll' => 2.50,
                'status' => 0
            ],
            [
                'name' => 'Sylhet Osmani International Airport',
                'description' => 'International airport serving Sylhet.',
                'from_tax_toll' => 4.00,
                'to_tax_toll' => 4.50,
                'status' => 0
            ],
        ];

        // Prepare the SQL statement for insertion
        $sql = "INSERT INTO airports (name, description, from_tax_toll, to_tax_toll, status, created_at, updated_at)
                VALUES (:name, :description, :from_tax_toll, :to_tax_toll, :status, :created_at, :updated_at)";
        $stmt = $this->db->prepare($sql);

        $seededCount = 0;
        foreach ($rawAirportsData as $airportData) {
            // Create an Airport model instance with new fields
            $airport = new Airport(
                id: null,
                name: $airportData['name'],
                description: $airportData['description'],
                fromTaxToll: $airportData['from_tax_toll'],
                toTaxToll: $airportData['to_tax_toll'],
                status: $airportData['status']
            );

            // Get the data in array format suitable for database insertion
            $dataToInsert = $airport->toArray();

            // Remove 'id' as it's auto-incremented
            unset($dataToInsert['id']);

            // Execute the statement
            $stmt->execute($dataToInsert);
            $seededCount++;
        }
        echo "Seeded " . $seededCount . " airports.\n";
    }
}