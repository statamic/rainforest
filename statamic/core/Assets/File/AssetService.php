<?php

namespace Statamic\Assets\File;

use Statamic\Contracts\Stache\AssetCacheService;
use Statamic\Contracts\Assets\AssetService as AssetServiceContract;

class AssetService implements AssetServiceContract
{
    /**
     * @var \Statamic\Contracts\Stache\AssetCacheService
     */
    private $cache;

    /**
     * @param \Statamic\Contracts\Stache\AssetCacheService $cache
     */
    public function __construct(AssetCacheService $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Get an asset by its UUID
     *
     * @param string      $uuid
     * @param string|null $locale
     * @return \Statamic\Data\Content
     */
    public function getUuid($uuid, $locale = null)
    {
        return $this->cache->get($uuid, $locale);
    }

    /**
     * Get assets from a folder
     *
     * @param string      $folder
     * @param string|null $locale
     * @return \Statamic\Assets\AssetCollection
     */
    public function getAssets($folder, $locale = null)
    {
        return $this->cache->getAssets($folder, $locale);
    }

    /**
     * @param string|null $locale
     * @return \Statamic\Contracts\Assets\AssetContainer[]
     */
    public function getContainers($locale = null)
    {
        return $this->cache->getContainers($locale);
    }

    /**
     * @param string      $uuid
     * @param string|null $locale
     * @return \Statamic\Contracts\Assets\AssetContainer
     */
    public function getContainer($uuid, $locale = null)
    {
        return $this->cache->getContainer($uuid, $locale);
    }
}
