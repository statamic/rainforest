<?php

namespace Statamic\Stache\File;

use Statamic\Contracts\Stache\LocalizedAssetCache as LocalizedAssetCacheContract;
use Statamic\Contracts\Stache\LocalizedAssetCacheService as LocalizedAssetCacheServiceContract;
use Statamic\Contracts\Stache\LocalizedAssetCacheUpdater as LocalizedAssetCacheUpdaterContract;

class LocalizedAssetCacheService implements LocalizedAssetCacheServiceContract
{
    /**
     * @var \Statamic\Contracts\Stache\LocalizedContentCacheUpdater
     */
    private $updater;

    /**
     * @param \Statamic\Contracts\Stache\LocalizedAssetCacheUpdater $updater
     */
    public function __construct(LocalizedAssetCacheUpdaterContract $updater)
    {
        $this->updater = $updater;
    }

    /**
     * @param \Statamic\Contracts\Stache\LocalizedAssetCache $cache
     * @return \Statamic\Contracts\Stache\LocalizedAssetCache
     */
    public function update(LocalizedAssetCacheContract $cache)
    {
        return $this->updater->update($cache);
    }
}
