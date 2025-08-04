<?php

namespace App\Repositories\MySQL;

use App\Models\GoogleApiKey;
use App\Repositories\Contracts\GoogleApiKeyInterface;
use PDO;
use PDOException;

class GoogleApiKeyDatabase implements GoogleApiKeyInterface
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Find a key by its ID.
     * @param int $id
     * @return GoogleApiKey|null
     * @throws PDOException
     */
    public function findById(int $id): ?GoogleApiKey
    {
        $stmt = $this->db->prepare("SELECT * FROM google_api_keys WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $airportData = $stmt->fetch();

        return $airportData ? GoogleApiKey::fromArray($airportData) : null;
    }

    /**
     * Find a key by its key-value.
     * @param string $apiKey
     * @return GoogleApiKey|null
     * @throws PDOException
     */
    public function findByApiKey(string $apiKey): ?GoogleApiKey
    {
        $stmt = $this->db->prepare("SELECT * FROM google_api_keys WHERE api_key = :api_key");
        $stmt->bindParam(':api_key', $apiKey, PDO::PARAM_STR);
        $stmt->execute();
        $keyData = $stmt->fetch(PDO::FETCH_ASSOC);

        return $keyData ? GoogleApiKey::fromArray($keyData) : null;
    }

    /**
     * Get all keys.
     * @return GoogleApiKey[]
     * @throws PDOException
     */
    public function all(): array
    {
        $stmt = $this->db->query("SELECT * FROM google_api_keys");
        $airportsData = $stmt->fetchAll();
        $airports = [];
        foreach ($airportsData as $airportData) {
            $airports[] = GoogleApiKey::fromArray($airportData);
        }
        return $airports;
    }

    /**
     * Get all active keys.
     * @return GoogleApiKey[]
     * @throws PDOException
     */
    public function allActive(): array
    {
        $stmt = $this->db->query("SELECT * FROM google_api_keys WHERE status = 1");
        $keysData = $stmt->fetchAll();
        $keys = [];
        foreach ($keysData as $keyData) {
            $keys[] = GoogleApiKey::fromArray($keyData);
        }
        return $keys;
    }

    /**
     * Get one active key.
     * @return GoogleApiKey|null
     * @throws PDOException
     */
    public function getOneActive(): ?GoogleApiKey
    {
        $stmt = $this->db->query("SELECT * FROM google_api_keys WHERE status = 1 LIMIT 1");
        $keyData = $stmt->fetch(PDO::FETCH_ASSOC);
        return $keyData ? GoogleApiKey::fromArray($keyData) : null;
    }

    /**
     * Create a new key.
     * @param GoogleApiKey $googleApiKey
     * @return int The ID of the newly created key.
     * @throws PDOException
     */
    public function create(GoogleApiKey $googleApiKey): int
    {
        $sql = "INSERT INTO google_api_keys (api_key, status, created_at, updated_at)
                VALUES (:api_key, :status, :created_at, :updated_at)";
        $stmt = $this->db->prepare($sql);

        $data = $googleApiKey->toArray();
        unset($data['id']); // ID is auto-incremented

        $stmt->execute($data);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Update an existing key.
     * @param GoogleApiKey $googleApiKey
     * @return bool True on success, false otherwise.
     * @throws PDOException
     */
    public function update(GoogleApiKey $googleApiKey): bool
    {
        $sql = "UPDATE google_api_keys SET
                    api_key = :api_key,
                    status = :status,
                    updated_at = :updated_at
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        $data = $googleApiKey->toArray();

        unset($data['created_at']);

        if (!isset($data['id'])) {
            throw new PDOException("Key ID is required for update.");
        }

        return $stmt->execute($data);
    }
}