<?php

namespace App\Database\Migrations;

use PDO;

class CreateBookingsTable
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function up(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS bookings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            service_type TINYINT(1) NOT NULL COMMENT '0=FromAirport, 1=ToAirport, 2=DoorToDoor',
            service_time TIMESTAMP NOT NULL,
            airport_id INT NULL,
            pickup_location VARCHAR(255) NULL,
            dropoff_location VARCHAR(255) NULL,
            number_of_passengers INT(1) NOT NULL,
            number_of_luggages INT(1) NOT NULL,
            car_id INT NOT NULL,
            distance DECIMAL(10, 2) NOT NULL COMMENT 'In Miles',
            total_payable DECIMAL(10, 2) NOT NULL,
            payment_status TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0=Pending, 1=Paid',
            passenger_name VARCHAR(100) NOT NULL,
            passenger_phone VARCHAR(15) NOT NULL,
            passenger_email VARCHAR(100) NULL,
            passenger_address VARCHAR(255) NULL,
            airline_name VARCHAR(100) NULL,
            flight_number VARCHAR(50) NULL,
            has_special_needs TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0=No, 1=Yes',
            is_traveller TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0=No, 1=Yes',
            status TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0=Inactive, 1=Active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (airport_id) REFERENCES airports(id) ON DELETE SET NULL,
            FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
        );";

        $this->db->exec($sql);
        echo "Table 'bookings' created.\n";
    }

    public function down(): void
    {
        $sql = "DROP TABLE IF EXISTS tunnel_charges;";
        $this->db->exec($sql);
        echo "Table 'bookings' dropped.\n";
    }
}