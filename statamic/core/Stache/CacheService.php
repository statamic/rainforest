<?php

namespace Statamic\Stache;

use Statamic\API\Config;
use Statamic\Events\StacheUpdated;
use Statamic\API\Cache as CacheAPI;
use Statamic\Contracts\Stache\Cache as CacheContract;
use Statamic\Contracts\Stache\CacheService as CacheServiceContract;
use Statamic\Contracts\Stache\UserCacheService as UserCacheServiceContract;
use Statamic\Contracts\Stache\AssetCacheService as AssetCacheServiceContract;
use Statamic\Contracts\Stache\ContentCacheService as ContentCacheServiceContract;

class CacheService implements CacheServiceContract
{
    /**
     * @var \Statamic\Contracts\Stache\Cache
     */
    private $cache;

    /**
     * @var \Statamic\Contracts\Stache\ContentCacheService
     */
    private $content_cacher;

    /**
     * @var \Statamic\Contracts\Stache\UserCacheService
     */
    private $user_cacher;

    /**
     * @var \Statamic\Contracts\Stache\AssetCacheService
     */
    private $asset_cacher;

    /**
     * @param \Statamic\Contracts\Stache\Cache               $cache
     * @param \Statamic\Contracts\Stache\ContentCacheService $content_cacher
     * @param \Statamic\Contracts\Stache\UserCacheService    $user_cacher
     * @param \Statamic\Contracts\Stache\AssetCacheService   $asset_cacher
     */
    public function __construct(
        CacheContract $cache,
        ContentCacheServiceContract $content_cacher,
        UserCacheServiceContract $user_cacher,
        AssetCacheServiceContract $asset_cacher
    ) {
        $this->cache = $cache;
        $this->content_cacher = $content_cacher;
        $this->user_cacher = $user_cacher;
        $this->asset_cacher = $asset_cacher;
    }

    /**
     * Update the cache
     */
    public function update()
    {
        $this->cache->setContent($this->content_cacher->update());
        $this->cache->setUsers($this->user_cacher->update());
        $this->cache->setAssets($this->asset_cacher->update());

        event('stache.updated', new StacheUpdated($this->cache));
    }

    /**
     * Load the cache, without updating it
     */
    public function load()
    {
        $this->cache->setContent($this->content_cacher->load());
        $this->cache->setUsers($this->user_cacher->load());
        $this->cache->setAssets($this->asset_cacher->load());
    }

    /**
     * @return \Statamic\Contracts\Stache\ContentCache
     */
    public function getContent()
    {
        return $this->cache->getContent();
    }

    /**
     * @return \Statamic\Contracts\Stache\UserCache
     */
    public function getUsers()
    {
        return $this->cache->getUsers();
    }

    /**
     * @return \Statamic\Contracts\Stache\AssetCache
     */
    public function getAssets()
    {
        return $this->cache->getAssets();
    }
}
