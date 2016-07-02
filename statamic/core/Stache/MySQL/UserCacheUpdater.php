<?php

namespace Statamic\Stache\MySQL;

use Statamic\Contracts\Stache\UserCache as UserCacheContract;
use Statamic\Contracts\Stache\UserCacheUpdater as UserCacheUpdaterContract;

class UserCacheUpdater implements UserCacheUpdaterContract
{
    /**
     * Update the cache
     *
     * @param \Statamic\Contracts\Stache\UserCache $cache
     * @return \Statamic\Contracts\Stache\UserCache
     */
    public function update(UserCacheContract $cache)
    {
        return $cache;
    }
}
