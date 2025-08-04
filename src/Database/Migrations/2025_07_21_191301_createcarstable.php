<?php

namespace App\Database\Migrations;

use PDO;

class CreateCarsTable
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function up(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS cars (
            id INT AUTO_INCREMENT PRIMARY KEY,
            regular_name VARCHAR(100) NOT NULL,
            short_name VARCHAR(100) NULL,
            color VARCHAR(30) NOT NULL,
            car_photo VARCHAR(255) NOT NULL,
            car_features TEXT NOT NULL,
            base_fare DECIMAL(10, 2) NOT NULL,
            minimum_fare DECIMAL(10, 2) NOT NULL,
            small_luggage_capacity TINYINT(1) NOT NULL,
            large_luggage_capacity TINYINT(1) NOT NULL,
            extra_luggage_capacity TINYINT(1) NOT NULL DEFAULT 0,
            num_of_passengers TINYINT(1) NOT NULL,
            is_child_seat TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0=NotAvailable, 1=Available',
            status TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0=Inactive, 1=Active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );";

        $this->db->exec($sql);
        echo "Table 'cars' created.\n";
    }

    public function down(): void
    {
        $sql = "DROP TABLE IF EXISTS cars;";
        $this->db->exec($sql);
        echo "Table 'cars' dropped.\n";
    }
}