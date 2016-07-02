<?php

namespace Statamic\Stache;

use Statamic\Contracts\Stache\Cache as CacheContract;
use Statamic\Contracts\Stache\UserCache as UserCacheContract;
use Statamic\Contracts\Stache\AssetCache as AssetCacheContract;
use Statamic\Contracts\Stache\ContentCache as ContentCacheContract;

class Cache implements CacheContract
{
    /**
     * @var \Statamic\Contracts\Stache\ContentCache
     */
    private $content;

    /**
     * @var \Statamic\Contracts\Stache\UserCache
     */
    private $users;

    /**
     * @var \Statamic\Contracts\Stache\AssetCache
     */
    private $assets;

    /**
     * @return \Statamic\Contracts\Stache\ContentCache
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param \Statamic\Contracts\Stache\ContentCache $cache
     */
    public function setContent(ContentCacheContract $cache)
    {
        $this->content = $cache;
    }

    /**
     * @return \Statamic\Contracts\Stache\UserCache
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param \Statamic\Contracts\Stache\UserCache $cache
     */
    public function setUsers(UserCacheContract $cache)
    {
        $this->users = $cache;
    }

    /**
     * @return \Statamic\Contracts\Stache\AssetCache
     */
    public function getAssets()
    {
        return $this->assets;
    }

    /**
     * @param \Statamic\Contracts\Stache\AssetCache $cache
     */
    public function setAssets(AssetCacheContract $cache)
    {
        $this->assets = $cache;
    }
}
