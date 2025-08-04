<?php

namespace App\Services;

use App\Models\GoogleApiKey;
use App\Repositories\Contracts\GoogleApiKeyInterface;
use App\Exceptions\ValidationException;
use App\Exceptions\DuplicateEntryException;

class GoogleApiKeyService
{
    private GoogleApiKeyInterface $googleApiKeyRepository;

    public function __construct(GoogleApiKeyInterface $googleApiKeyRepository)
    {
        $this->googleApiKeyRepository = $googleApiKeyRepository;
    }

    /**
     * Get all keys.
     * @return GoogleApiKey[]
     */
    public function getAllKeys(): array
    {
        return $this->googleApiKeyRepository->all();
    }

    /**
     * Get all active keys.
     * @return GoogleApiKey[]
     */
    public function getActiveKeys(): array
    {
        return $this->googleApiKeyRepository->allActive();
    }

    /**
     * Get one active Google API key.
     * @return GoogleApiKey|null
     */
    public function getOneActiveKey(): ?GoogleApiKey
    {
        return $this->googleApiKeyRepository->getOneActive();
    }

    /**
     * Get an googleapikey by ID.
     * @param int $id
     * @return GoogleApiKey|null
     */
    public function getKeyById(int $id): ?GoogleApiKey
    {
        return $this->googleApiKeyRepository->findById($id);
    }

    /**
     * Create a new key.
     * @param array $data
     * @return GoogleApiKey
     * @throws DuplicateEntryException If a key with the same name already exists.
     * @throws \Exception If creation fails.
     */
    public function createKey(array $data): GoogleApiKey
    {
        // Check for duplicate name before creating
        if (isset($data['api_key']) && $this->googleApiKeyRepository->findByApiKey($data['api_key'])) {
            throw new DuplicateEntryException("A key with the name '{$data['api_key']}' already exists.");
        }

        $googleapikey = new GoogleApiKey(
            id: null,
            apiKey: $data['api_key'],
            status: (int) $data['status']
        );

        $googleapikeyId = $this->googleApiKeyRepository->create($googleapikey);
        if (!$googleapikeyId) {
            throw new \Exception("Failed to create the key.");
        }

        $googleapikey->id = $googleapikeyId;

        return $googleapikey;
    }

    /**
     * Update an existing key.
     * @param int $id
     * @param array $data
     * @return GoogleApiKey
     * @throws \Exception If googleapikey not found or update fails.
     * @throws DuplicateEntryException If updating to a name that already exists for another googleapikey.
     */
    public function updateGoogleApiKey(int $id, array $data): GoogleApiKey
    {
        $googleapikey = $this->googleApiKeyRepository->findById($id);

        if (!$googleapikey) {
            throw new \Exception("Key not found.");
        }

        // Check for duplicate name only if name is being updated
        if (isset($data['api_key']) && $data['api_key'] !== $googleapikey->apiKey) {
            $existingGoogleApiKey = $this->googleApiKeyRepository->findByApiKey($data['api_key']);
            if ($existingGoogleApiKey && $existingGoogleApiKey->id !== $id) {
                throw new DuplicateEntryException("An googleapikey with the name '{$data['api_key']}' already exists.");
            }
        }

        if (isset($data['api_key'])) $googleapikey->apiKey = $data['api_key'];
        if (isset($data['status'])) $googleapikey->status = (int) $data['status'];

        $googleapikey->updatedAt = date('Y-m-d H:i:s');

        if (!$this->googleApiKeyRepository->update($googleapikey)) {
            throw new \Exception("Failed to update the key.");
        }

        return $googleapikey;
    }

    /**
     * Get the zip code by location name.
     *
     * @param string $locationName
     * @return string|null
     */
    public function getZipCodeByLocationName(string $locationName): ?string
    {
        // Make sure you have the Google API key available here.
        $apiKey = $this->getOneActiveKey(); 
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($locationName) . "&key=" . $apiKey;

        // Use cURL or file_get_contents to make the API call
        $response = file_get_contents($url);
        $data = json_decode($response, true);

        // Check if the API call was successful
        if ($data['status'] !== 'OK' || empty($data['results'])) {
            return null;
        }

        // Extract the zip code from the API response
        foreach ($data['results'][0]['address_components'] as $component) {
            if (in_array('postal_code', $component['types'])) {
                return $component['long_name'];
            }
        }

        return null;
    }
}