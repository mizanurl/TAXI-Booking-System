<?php

namespace App\Database\Migrations;

use PDO;

class DropChargeEndDateFromBookingsTable
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function up(): void
    {
        $sql = "ALTER TABLE bookings DROP COLUMN charge_end_date;";
        $this->db->exec($sql);
        echo "Column 'charge_end_date' dropped from 'bookings' table.\n";
    }

    public function down(): void
    {
        // Re-adding the column in 'down' method for rollback capability.
        // You might need to adjust the default value or NULLability based on your application's needs
        // if this column was populated with meaningful data before dropping.
        $sql = "ALTER TABLE bookings ADD COLUMN charge_end_date DATE NOT NULL AFTER service_time;";
        $this->db->exec($sql);
        echo "Column 'charge_end_date' re-added to 'bookings' table.\n";
    }
}