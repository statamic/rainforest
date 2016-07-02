<?php

namespace Statamic\Contracts\Stache;

interface AssetCacheUpdater
{
    /**
     * Update the cache
     *
     * @param \Statamic\Contracts\Stache\AssetCache $cache
     * @return \Statamic\Contracts\Stache\AssetCache
     */
    public function update(AssetCache $cache);

    /**
     * Load the cache
     *
     * @param \Statamic\Contracts\Stache\AssetCache $cache
     * @return \Statamic\Contracts\Stache\AssetCache
     */
    public function load(AssetCache $cache);
}
