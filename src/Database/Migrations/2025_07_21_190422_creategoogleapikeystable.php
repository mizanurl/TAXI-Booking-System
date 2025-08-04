<?php

namespace App\Database\Migrations;

use PDO;

class CreateGoogleApiKeysTable
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function up(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS google_api_keys (
            id INT AUTO_INCREMENT PRIMARY KEY,
            api_key VARCHAR(255) NOT NULL,
            status TINYINT(1) NOT NULL COMMENT '0=Inactive, 1=Active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );";

        $this->db->exec($sql);
        echo "Table 'google_api_keys' created.\n";
    }

    public function down(): void
    {
        $sql = "DROP TABLE IF EXISTS google_api_keys;";
        $this->db->exec($sql);
        echo "Table 'google_api_keys' dropped.\n";
    }
}