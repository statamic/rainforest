<?php

namespace Statamic\Contracts\Stache;

interface CacheService
{
    /**
     * Update the cache
     */
    public function update();

    /**
     * @return \Statamic\Contracts\Stache\ContentCache
     */
    public function getContent();

    /**
     * @return \Statamic\Contracts\Stache\UserCache
     */
    public function getUsers();

    /**
     * @return \Statamic\Contracts\Stache\AssetCache
     */
    public function getAssets();
}
