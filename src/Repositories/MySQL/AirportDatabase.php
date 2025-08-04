<?php

namespace App\Repositories\MySQL;

use App\Models\Airport;
use App\Repositories\Contracts\AirportInterface;
use PDO;
use PDOException;

class AirportDatabase implements AirportInterface
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Find an airport by its ID.
     * @param int $id
     * @return Airport|null
     * @throws PDOException
     */
    public function findById(int $id): ?Airport
    {
        // Ensure all columns are selected including the new ones
        $stmt = $this->db->prepare("SELECT id, name, description, from_tax_toll, to_tax_toll, status, created_at, updated_at FROM airports WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $airportData = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch as associative array

        return $airportData ? Airport::fromArray($airportData) : null;
    }

    /**
     * Find an airport by its name.
     * @param string $name
     * @return Airport|null
     * @throws PDOException
     */
    public function findByName(string $name): ?Airport
    {
        // Ensure all columns are selected including the new ones
        $stmt = $this->db->prepare("SELECT id, name, description, from_tax_toll, to_tax_toll, status, created_at, updated_at FROM airports WHERE name = :name");
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->execute();
        $airportData = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch as associative array

        return $airportData ? Airport::fromArray($airportData) : null;
    }

    /**
     * Get all airports.
     * @return Airport[]
     * @throws PDOException
     */
    public function all(): array
    {
        // Ensure all columns are selected including the new ones
        $stmt = $this->db->query("SELECT id, name, description, from_tax_toll, to_tax_toll, status, created_at, updated_at FROM airports");
        $airportsData = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch as associative array
        $airports = [];
        foreach ($airportsData as $airportData) {
            $airports[] = Airport::fromArray($airportData);
        }
        return $airports;
    }

    /**
     * Get all active airports.
     * @return Airport[]
     * @throws PDOException
     */
    public function allActive(): array
    {
        // Ensure all columns are selected including the new ones
        $stmt = $this->db->query("SELECT id, name, description, from_tax_toll, to_tax_toll, status, created_at, updated_at FROM airports WHERE status = 1");
        $airportsData = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch as associative array
        $airports = [];
        foreach ($airportsData as $airportData) {
            $airports[] = Airport::fromArray($airportData);
        }
        return $airports;
    }

    /**
     * Create a new airport.
     * @param Airport $airport
     * @return int The ID of the newly created airport.
     * @throws PDOException
     */
    public function create(Airport $airport): int
    {
        $sql = "INSERT INTO airports (name, description, from_tax_toll, to_tax_toll, status, created_at, updated_at)
                VALUES (:name, :description, :from_tax_toll, :to_tax_toll, :status, :created_at, :updated_at)";
        $stmt = $this->db->prepare($sql);

        $data = $airport->toArray();
        unset($data['id']); // ID is auto-incremented

        $stmt->execute([
            ':name' => $data['name'],
            ':description' => $data['description'],
            ':from_tax_toll' => $data['from_tax_toll'],
            ':to_tax_toll' => $data['to_tax_toll'],
            ':status' => $data['status'],
            ':created_at' => $data['created_at'],
            ':updated_at' => $data['updated_at'],
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Update an existing airport.
     * @param Airport $airport
     * @return bool True on success, false otherwise.
     * @throws PDOException
     */
    public function update(Airport $airport): bool
    {
        $sql = "UPDATE airports SET
                    name = :name,
                    description = :description,
                    from_tax_toll = :from_tax_toll,
                    to_tax_toll = :to_tax_toll,
                    status = :status,
                    updated_at = :updated_at
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        $data = $airport->toArray();

        // created_at is not updated
        unset($data['created_at']);

        if (!isset($data['id'])) {
            throw new PDOException("Airport ID is required for update.");
        }

        return $stmt->execute([
            ':id' => $data['id'],
            ':name' => $data['name'],
            ':description' => $data['description'],
            ':from_tax_toll' => $data['from_tax_toll'],
            ':to_tax_toll' => $data['to_tax_toll'],
            ':status' => $data['status'],
            ':updated_at' => $data['updated_at'],
        ]);
    }

    /**
     * Delete an airport by its ID.
     * @param int $id
     * @return bool True on success, false otherwise.
     * @throws PDOException
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM airports WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}