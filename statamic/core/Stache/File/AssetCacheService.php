<?php

namespace Statamic\Stache\File;

use Statamic\API\Str;
use Statamic\Assets\AssetCollection;
use Statamic\Contracts\Stache\AssetCache as AssetCacheContract;
use Statamic\Contracts\Stache\AssetCacheService as AssetCacheServiceContract;
use Statamic\Contracts\Stache\AssetCacheUpdater as AssetCacheUpdaterContract;

class AssetCacheService implements AssetCacheServiceContract
{
    /**
     * @var \Statamic\Contracts\Stache\AssetCache
     */
    private $cache;

    /**
     * @var \Statamic\Contracts\Stache\AssetCacheUpdater
     */
    private $updater;

    /**
     * @param \Statamic\Contracts\Stache\AssetCache        $cache
     * @param \Statamic\Contracts\Stache\AssetCacheUpdater $updater
     */
    public function __construct(AssetCacheContract $cache, AssetCacheUpdaterContract $updater)
    {
        $this->cache = $cache;
        $this->updater = $updater;
    }

    /**
     * Load the cache
     *
     * @return \Statamic\Contracts\Stache\AssetCache
     */
    public function load()
    {
        return $this->updater->load($this->cache);
    }

    /**
     * Update the cache
     *
     * @return \Statamic\Contracts\Stache\AssetCache
     */
    public function update()
    {
        return $this->updater->update($this->cache);
    }

    /**
     * Get asset by UUID
     *
     * @param string      $uuid
     * @param string|null $locale
     * @return mixed
     */
    public function get($uuid, $locale = null)
    {
        return $this->cache->getLocale($locale)->getAsset($uuid);
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
        $folders = collect($this->cache->getLocale($locale)->getAssets());

        if (! $asset_folder = $folders->get($folder)) {
            // If there's no matching folder, send back an empty collection
            return new AssetCollection;
        }

        return $asset_folder->assets();
    }

    /**
     * Get the names of asset folders
     *
     * @param string|null $folder Folder to search within
     * @param string|null $locale
     * @return array
     */
    public function getFolders($folder = null, $locale = null)
    {
        $folders = collect($this->cache->getLocale($locale)->getAssets());

        return $folders->filter(function($asset_folder) use ($folder) {
            if ($folder == '/') {
                // If we're getting the root assets level, we know we don't want the root folder.
                if ($asset_folder->path() === '/') {
                    return false;
                }

                // We only want folders with no slashes.
                $slashes = 0;

            } else {
                // If we're getting a nested folder, we don't want any folders that don't start with ours.
                if (! Str::startsWith($asset_folder->path(), $folder . '/')) {
                    return false;
                }

                // We only want folders one level deeper.
                $slashes = substr_count($folder, '/') + 1;
            }

            return $slashes === substr_count($asset_folder->path(), '/');
        })->all();
    }

    /**
     * Get all the asset folders
     *
     * @param string|null $locale
     * @return \Statamic\Contracts\Data\AssetFolder[]
     */
    public function getAllFolders($locale = null)
    {
        return $this->cache->getLocale($locale)->getAssets();
    }

    /**
     * Get a specific asset folder
     *
     * @param string       $folder
     * @param string|null  $locale
     * @return mixed
     */
    public function getFolder($folder, $locale = null)
    {
        return array_get($this->getAllFolders($locale), $folder);
    }

    /**
     * @param string|null $locale
     * @return \Statamic\Contracts\Assets\AssetContainer[]
     */
    public function getContainers($locale = null)
    {
        return $this->cache->getLocale($locale)->getAssetContainers();
    }

    /**
     * @param string      $uuid
     * @param string|null $locale
     * @return \Statamic\Contracts\Assets\AssetContainer
     */
    public function getContainer($uuid, $locale = null)
    {
        return $this->cache->getLocale($locale)->getAssetContainer($uuid);
    }
}
