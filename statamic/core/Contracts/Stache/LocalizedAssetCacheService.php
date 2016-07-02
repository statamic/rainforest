<?php

namespace Statamic\Contracts\Stache;

interface LocalizedAssetCacheService
{
    /**
     * @param \Statamic\Contracts\Stache\LocalizedAssetCacheUpdater $updater
     */
    public function __construct(LocalizedAssetCacheUpdater $updater);

    /**
     * @param \Statamic\Contracts\Stache\LocalizedAssetCache $cache
     * @return \Statamic\Contracts\Stache\LocalizedAssetCache
     */
    public function update(LocalizedAssetCache $cache);
}
