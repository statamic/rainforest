<?php

namespace Statamic\Contracts\Stache;

interface ContentCacheUpdater
{
    /**
     * Update the cache
     *
     * @param \Statamic\Contracts\Stache\ContentCache $cache
     * @return \Statamic\Contracts\Stache\ContentCache
     */
    public function update(ContentCache $cache);

    /**
     * Load the cache
     *
     * @param \Statamic\Contracts\Stache\ContentCache $cache
     * @return \Statamic\Contracts\Stache\ContentCache
     */
    public function load(ContentCache $cache);
}
