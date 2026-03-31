<?php

namespace App\Console\Commands;

use App\Services\CacheService;
use Illuminate\Console\Command;

class clearAppCache extends Command
{
    protected $signature = 'app:clear-cache';
    protected $description = 'Clear all application-level cache (dashboard stats dropdowns)';

    public function handle(CacheService $cache): int
    {
        $cache->clearAll();
        $this->info('Application cache cleared.');
        return 0;

    }
}
