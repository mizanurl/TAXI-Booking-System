<?php

namespace App\Database\Seeders;

use PDO;
use App\Models\SmsService;

class SmsServiceSeeder
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function run(): void
    {
        // Check if table is empty to prevent duplicate seeding
        $stmt = $this->db->query("SELECT COUNT(*) FROM sms_services");
        if ($stmt->fetchColumn() > 0) {
            echo "sms_services table is not empty. Skipping SmsServiceSeeder.\n";
            return;
        }

        // Define raw data for sms_services
        $rawSmsData = [
            [
                'phone_number' => '+6172306362',
                'status' => 1
            ],
            [
                'phone_number' => '+8577772125',
                'status' => 1
            ],
        ];

        // Prepare the SQL statement for insertion
        // We include created_at and updated_at as they are part of the model's toArray() output
        $sql = "INSERT INTO sms_services (phone_number, status, created_at, updated_at)
                VALUES (:phone_number, :status, :created_at, :updated_at)";
        $stmt = $this->db->prepare($sql);

        $seededCount = 0;
        foreach ($rawSmsData as $smsData) {
            // Create an SmsService model instance
            // We pass null for id, and let the model handle created_at/updated_at defaults
            $smsService = new SmsService(
                id: null,
                phoneNumber: $smsData['phone_number'],
                status: $smsData['status']
            );

            // Get the data in array format suitable for database insertion
            $dataToInsert = $smsService->toArray();

            // Remove 'id' as it's auto-incremented
            unset($dataToInsert['id']);

            // Execute the statement
            $stmt->execute($dataToInsert);
            $seededCount++;
        }
        echo "Seeded " . $seededCount . " sms_services.\n";
    }
}