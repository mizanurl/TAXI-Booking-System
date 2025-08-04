<?php

namespace App\Database\Migrations;

use PDO;

class CreateSquareSettingsTable
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function up(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS square_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            application_id VARCHAR(255) NOT NULL,
            access_token VARCHAR(255) NOT NULL,
            account_email VARCHAR(255) NULL COMMENT 'Square account email',
            location_id VARCHAR(255) NOT NULL COMMENT 'Square Location ID associated with payments',
            currency VARCHAR(10) NOT NULL DEFAULT 'USD',
            webhook_signature_key VARCHAR(255) NULL COMMENT 'Webhook signature key used to verify incoming events',
            webhook_url VARCHAR(255) NULL COMMENT 'URL to which Square sends payment notifications',
            environment TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0=Sandbox, 1=Live',
            status TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0=Inactive, 1=Active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );";

        $this->db->exec($sql);
        echo "Table 'square_settings' created.\n";
    }

    public function down(): void
    {
        $sql = "DROP TABLE IF EXISTS square_settings;";
        $this->db->exec($sql);
        echo "Table 'square_settings' dropped.\n";
    }
}