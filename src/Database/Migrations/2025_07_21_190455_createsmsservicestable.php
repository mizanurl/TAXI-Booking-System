<?php

namespace App\Database\Migrations;

use PDO;

class CreateSmsServicesTable
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function up(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS sms_services (
            id INT AUTO_INCREMENT PRIMARY KEY,
            phone_number VARCHAR(20) NOT NULL,
            status TINYINT(1) NOT NULL COMMENT '0=Inactive, 1=Active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );";

        $this->db->exec($sql);
        echo "Table 'sms_services' created.\n";
    }

    public function down(): void
    {
        $sql = "DROP TABLE IF EXISTS sms_services;";
        $this->db->exec($sql);
        echo "Table 'sms_services' dropped.\n";
    }
}