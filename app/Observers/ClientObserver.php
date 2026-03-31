<?php

namespace App\Observers;

use App\Models\Client;
use App\Services\CacheService;

class ClientObserver
{
    /**
     * Create a new class instance.
     */
    public function __construct(private CacheService $cache) {}

    public function created(Client $client): void
    {
        $this->cache->invalidateClientCaches();
    
    }

    public function updated(Client $client): void
    {
        $this->cache->invalidateClientCaches();
    }

    public function deleted(Client $client): void
    {
        $this->cache->invalidateClientCaches();
    }
}
