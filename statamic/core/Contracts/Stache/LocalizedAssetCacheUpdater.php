<?php

namespace Statamic\Contracts\Stache;

interface LocalizedAssetCacheUpdater
{
    /**
     * @param \Statamic\Contracts\Stache\AssetCache $cache
     */
    public function __construct(AssetCache $cache);

    /**
     * @param \Statamic\Contracts\Stache\LocalizedAssetCache $local_cache
     * @return \Statamic\Contracts\Stache\LocalizedAssetCache
     */
    public function update(LocalizedAssetCache $local_cache);
}
