<?php

namespace App\Database\Migrations;

use PDO;

class CreatePaypalSettingsTable
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function up(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS paypal_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            client_id VARCHAR(255) NOT NULL,
            client_secret VARCHAR(255) NOT NULL,
            account_email VARCHAR(255) NULL COMMENT 'PayPal account email',
            currency VARCHAR(10) NOT NULL DEFAULT 'USD',
            webhook_id VARCHAR(255) NULL COMMENT 'Webhook ID used to verify incoming events',
            webhook_url VARCHAR(255) NULL COMMENT 'URL PayPal will notify (for IPN or Webhooks)',
            environment TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0=Sandbox, 1=Live',
            status TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0=Inactive, 1=Active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );";

        $this->db->exec($sql);
        echo "Table 'paypal_settings' created.\n";
    }

    public function down(): void
    {
        $sql = "DROP TABLE IF EXISTS paypal_settings;";
        $this->db->exec($sql);
        echo "Table 'paypal_settings' dropped.\n";
    }
}