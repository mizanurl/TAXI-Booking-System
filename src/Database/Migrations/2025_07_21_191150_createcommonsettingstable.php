<?php

namespace App\Database\Migrations;

use PDO;

class CreateCommonSettingsTable
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function up(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS common_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            company_name VARCHAR(100) NOT NULL,
            company_logo VARCHAR(255) NULL,
            address TEXT NOT NULL,
            booking_call_number VARCHAR(20) NULL,
            telephone_number VARCHAR(20) NOT NULL,
            email VARCHAR(100) NOT NULL,
            website VARCHAR(100) NULL,
            holidays TEXT NULL,
            holiday_surcharge DECIMAL(10, 2) NULL,
            tunnel_charge DECIMAL(10, 2) NULL DEFAULT 0.00,
            gratuity DECIMAL(10, 2) NULL DEFAULT 0.00,
            stop_over_charge DECIMAL(10, 2) NULL DEFAULT 0.00,
            infant_front_facing_seat_charge DECIMAL(10, 2) NULL DEFAULT 0.00,
            infant_rear_facing_seat_charge DECIMAL(10, 2) NULL DEFAULT 0.00,
            infant_booster_seat_charge DECIMAL(10, 2) NULL DEFAULT 0.00,
            night_charge DECIMAL(10, 2) NULL DEFAULT 0.00,
            night_charge_start_time TIME NULL,
            night_charge_end_time TIME NULL,
            hidden_night_charge DECIMAL(10, 2) NULL DEFAULT 0.00,
            hidden_night_charge_start_time TIME NULL,
            hidden_night_charge_end_time TIME NULL,
            snow_strom_charge DECIMAL(10, 2) NULL DEFAULT 0.00,
            rush_hour_charge DECIMAL(10, 2) NULL DEFAULT 0.00,
            extra_luggage_charge DECIMAL(10, 2) NULL DEFAULT 0.00,
            pets_charge DECIMAL(10, 2) NULL DEFAULT 0.00,
            convenience_fee DECIMAL(10, 2) NULL DEFAULT 0.00 COMMENT 'percentage of total amount',
            cash_discount DECIMAL(10, 2) NULL DEFAULT 0.00 COMMENT 'percentage of total amount',
            paypal_charge DECIMAL(10, 2) NULL DEFAULT 0.00 COMMENT 'percentage of total amount',
            square_charge DECIMAL(10, 2) NULL DEFAULT 0.00 COMMENT 'percentage of total amount',
            credit_card_charge DECIMAL(10, 2) NULL DEFAULT 0.00 COMMENT 'percentage of total amount',
            status TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0=Inactive, 1=Active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );";

        $this->db->exec($sql);
        echo "Table 'common_settings' created.\n";
    }

    public function down(): void
    {
        $sql = "DROP TABLE IF EXISTS common_settings;";
        $this->db->exec($sql);
        echo "Table 'common_settings' dropped.\n";
    }
}