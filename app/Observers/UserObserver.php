<?php

namespace App\Observers;

use App\Models\User;
use App\Services\CacheService;

class UserObserver
{
    /**
     * Create a new class instance.
     */
    public function __construct(private CacheService $cache) {}

    public function created(User $user): void
    {
        $this->cache->invalidateUserCaches();
    }

    public function updated(User $user): void
    {
        $this->cache->invalidateUserCaches();
    }

    public function deleted(User $user): void
    {
        $this->cache->invalidateUserCaches();
    }

    
}
