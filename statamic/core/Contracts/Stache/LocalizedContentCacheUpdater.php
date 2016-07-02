<?php

namespace Statamic\Contracts\Stache;

interface LocalizedContentCacheUpdater
{
    /**
     * @param \Statamic\Contracts\Stache\ContentCache $cache
     */
    public function __construct(ContentCache $cache);

    /**
     * @param \Statamic\Contracts\Stache\LocalizedContentCache $local_cache
     * @return \Statamic\Contracts\Stache\LocalizedContentCache
     */
    public function update(LocalizedContentCache $local_cache);
}
