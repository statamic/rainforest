<?php

namespace Statamic\Contracts\Stache;

interface UserCacheUpdater
{
    /**
     * Update the cache
     *
     * @param \Statamic\Contracts\Stache\UserCache $cache
     * @return \Statamic\Contracts\Stache\UserCache
     */
    public function update(UserCache $cache);

    /**
     * Load the cache
     *
     * @param \Statamic\Contracts\Stache\UserCache $cache
     * @return \Statamic\Contracts\Stache\UserCache
     */
    public function load(UserCache $cache);
}
