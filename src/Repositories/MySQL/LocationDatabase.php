<?php

namespace App\Repositories\MySQL;

use App\Models\GoogleApiKey;
use App\Repositories\Contracts\LocationInterface;
use PDO;
use PDOException;

class LocationDatabase implements LocationInterface
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Get the latest active Google API Key.
     * We assume "latest" means the one with the highest ID or most recent updated_at/created_at.
     * For simplicity, we'll order by ID descending and limit to 1.
     *
     * @return GoogleApiKey|null
     * @throws PDOException
     */
    public function getLatestActiveKey(): ?GoogleApiKey
    {
        // Query to get the latest active API key
        // Assuming 'status = 1' means active and ordering by 'id' DESC gets the latest.
        $sql = "SELECT * FROM google_api_keys WHERE status = 1 ORDER BY id DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);

        $stmt->execute();
        $keyData = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch as associative array

        if ($keyData) {
            return GoogleApiKey::fromArray($keyData);
        }

        return null;
    }
}