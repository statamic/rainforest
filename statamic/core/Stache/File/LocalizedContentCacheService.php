<?php

namespace Statamic\Stache\File;

use Statamic\Contracts\Stache\LocalizedContentCache as LocalizedContentCacheContract;
use Statamic\Contracts\Stache\LocalizedContentCacheService as LocalizedContentCacheServiceContract;
use Statamic\Contracts\Stache\LocalizedContentCacheUpdater as LocalizedContentCacheUpdaterContract;

class LocalizedContentCacheService implements LocalizedContentCacheServiceContract
{
    /**
     * @var \Statamic\Contracts\Stache\LocalizedContentCacheUpdater
     */
    private $updater;

    /**
     * @param \Statamic\Contracts\Stache\LocalizedContentCacheUpdater $updater
     */
    public function __construct(LocalizedContentCacheUpdaterContract $updater)
    {
        $this->updater = $updater;
    }

    /**
     * @param \Statamic\Contracts\Stache\LocalizedContentCache $cache
     * @return \Statamic\Contracts\Stache\LocalizedContentCache
     */
    public function update(LocalizedContentCacheContract $cache)
    {
        return $this->updater->update($cache);
    }
}
