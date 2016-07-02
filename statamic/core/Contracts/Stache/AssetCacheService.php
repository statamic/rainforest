<?php

namespace Statamic\Contracts\Stache;

interface AssetCacheService
{
    /**
     * Update the cache
     *
     * @return \Statamic\Contracts\Stache\AssetCache
     */
    public function update();

    /**
     * Load the cache
     *
     * @return \Statamic\Contracts\Stache\AssetCache
     */
    public function load();

    /**
     * Get asset by UUID
     *
     * @param string      $uuid
     * @param string|null $locale
     * @return mixed
     */
    public function get($uuid, $locale = null);

    /**
     * Get assets from a folder
     *
     * @param string      $folder
     * @param string|null $locale
     * @return \Statamic\Assets\AssetCollection
     */
    public function getAssets($folder, $locale = null);

    /**
     * Get the names of asset folders
     *
     * @param string|null $folder Folder to search within
     * @param string|null $locale
     * @return array
     */
    public function getFolders($folder, $locale = null);

    /**
     * Get the names of all asset folders
     *
     * @param string|null $locale
     * @return array
     */
    public function getAllFolders($locale = null);

    /**
     * Get a folder
     *
     * @param string      $folder
     * @param string|null $locale
     * @return mixed
     */
    public function getFolder($folder, $locale = null);

    /**
     * @param string|null $locale
     * @return \Statamic\Contracts\Assets\AssetContainer[]
     */
    public function getContainers($locale = null);

    /**
     * @param string      $uuid
     * @param string|null $locale
     * @return \Statamic\Contracts\Assets\AssetContainer
     */
    public function getContainer($uuid, $locale = null);
}
