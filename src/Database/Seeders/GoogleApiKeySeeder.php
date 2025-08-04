<?php

namespace App\Database\Seeders;

use PDO;
use App\Models\GoogleApiKey;

class GoogleApiKeySeeder
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function run(): void
    {
        // Check if table is empty to prevent duplicate seeding
        $stmt = $this->db->query("SELECT COUNT(*) FROM google_api_keys");
        if ($stmt->fetchColumn() > 0) {
            echo "google_api_keys table is not empty. Skipping GoogleApiKeySeeder.\n";
            return;
        }

        // Define raw data for google_api_keys
        $rawKeysData = [
            [
                'api_key' => 'AIzaSyAWNxnxv2yBAsB4xuWG4yKBiOCHq66Jn8A',
                'status' => 1
            ],
        ];

        // Prepare the SQL statement for insertion
        // We include created_at and updated_at as they are part of the model's toArray() output
        $sql = "INSERT INTO google_api_keys (api_key, status, created_at, updated_at)
                VALUES (:api_key, :status, :created_at, :updated_at)";
        $stmt = $this->db->prepare($sql);

        $seededCount = 0;
        foreach ($rawKeysData as $keyData) {
            // Create an GoogleApiKey model instance
            // We pass null for id, and let the model handle created_at/updated_at defaults
            $apiKey = new GoogleApiKey(
                id: null,
                apiKey: $keyData['api_key'],
                status: $keyData['status']
            );

            // Get the data in array format suitable for database insertion
            $dataToInsert = $apiKey->toArray();

            // Remove 'id' as it's auto-incremented
            unset($dataToInsert['id']);

            // Execute the statement
            $stmt->execute($dataToInsert);
            $seededCount++;
        }
        echo "Seeded " . $seededCount . " google_api_keys.\n";
    }
}