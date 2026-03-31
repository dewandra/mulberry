<?php

namespace App\Observers;

use App\Models\Project;
use App\Services\CacheService;

class ProjectObserver
{
    /**
     * Create a new class instance.
     */
    public function __construct(private CacheService $cache) {}

    public function created(Project $project): void
    {
        $this->cache->invalidateProjectCaches();
    }

    public function updated(Project $project): void
    {
        $this->cache->invalidateProjectCaches();
    }

    public function deleted(Project $project): void
    {
        $this->cache->invalidateProjectCaches();
    }
}
