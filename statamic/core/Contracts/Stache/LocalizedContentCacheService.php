<?php

namespace Statamic\Contracts\Stache;

interface LocalizedContentCacheService
{
    /**
     * @param \Statamic\Contracts\Stache\LocalizedContentCacheUpdater $updater
     */
    public function __construct(LocalizedContentCacheUpdater $updater);

    /**
     * @param \Statamic\Contracts\Stache\LocalizedContentCache $cache
     * @return \Statamic\Contracts\Stache\LocalizedContentCache
     */
    public function update(LocalizedContentCache $cache);
}
