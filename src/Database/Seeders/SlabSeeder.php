<?php

namespace App\Database\Seeders;

use PDO;
use App\Models\Slab;

class SlabSeeder
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function run(): void
    {
        // Check if table is empty to prevent duplicate seeding
        $stmt = $this->db->query("SELECT COUNT(*) FROM slabs");
        if ($stmt->fetchColumn() > 0) {
            echo "slabs table is not empty. Skipping SlabSeeder.\n";
            return;
        }

        // Define raw data for slabs
        $rawSlabsData = [
            [
                'slab_value' => 6.00,
                'slab_unit' => 0,
                'slab_type' => 0,
                'status' => 1
            ],
            [
                'slab_value' => 9.00,
                'slab_unit' => 0,
                'slab_type' => 0,
                'status' => 1
            ],
            [
                'slab_value' => 11.00,
                'slab_unit' => 0,
                'slab_type' => 0,
                'status' => 1
            ],
            [
                'slab_value' => 19.00,
                'slab_unit' => 0,
                'slab_type' => 0,
                'status' => 1
            ],
            [
                'slab_value' => 25.00,
                'slab_unit' => 0,
                'slab_type' => 0,
                'status' => 1
            ],
            [
                'slab_value' => 100.00,
                'slab_unit' => 0,
                'slab_type' => 0,
                'status' => 1
            ],
            [
                'slab_value' => 2.00,
                'slab_unit' => 1,
                'slab_type' => 1,
                'status' => 1
            ],
            [
                'slab_value' => 5.00,
                'slab_unit' => 1,
                'slab_type' => 1,
                'status' => 1
            ],
            [
                'slab_value' => 10.00,
                'slab_unit' => 1,
                'slab_type' => 1,
                'status' => 1
            ],
            [
                'slab_value' => 15.00,
                'slab_unit' => 1,
                'slab_type' => 1,
                'status' => 1
            ],
            [
                'slab_value' => 20.00,
                'slab_unit' => 1,
                'slab_type' => 1,
                'status' => 1
            ],
            [
                'slab_value' => 24.00,
                'slab_unit' => 1,
                'slab_type' => 1,
                'status' => 1
            ],
        ];

        // Prepare the SQL statement for insertion
        // We include created_at and updated_at as they are part of the model's toArray() output
        $sql = "INSERT INTO slabs (slab_value, slab_unit, slab_type, status, created_at, updated_at)
                VALUES (:slab_value, :slab_unit, :slab_type, :status, :created_at, :updated_at)";
        $stmt = $this->db->prepare($sql);

        $seededCount = 0;
        foreach ($rawSlabsData as $slabData) {
            // Create a Slab model instance
            // We pass null for id, and let the model handle created_at/updated_at defaults
            $slab = new Slab(
                id: null,
                slabValue: $slabData['slab_value'],
                slabUnit: $slabData['slab_unit'],
                slabType: $slabData['slab_type'],
                status: $slabData['status']
            );

            // Get the data in array format suitable for database insertion
            $dataToInsert = $slab->toArray();

            // Remove 'id' as it's auto-incremented
            unset($dataToInsert['id']);

            // Execute the statement
            $stmt->execute($dataToInsert);
            $seededCount++;
        }
        echo "Seeded " . $seededCount . " slabs.\n";
    }
}