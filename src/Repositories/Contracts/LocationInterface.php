<?php

namespace App\Repositories\Contracts;

use App\Models\GoogleApiKey;

/**
 * Interface for Location Repository.
 */
interface LocationInterface
{
    /**
     * Get the latest active Google API Key.
     * @return GoogleApiKey|null
     */
    public function getLatestActiveKey(): ?GoogleApiKey;
}