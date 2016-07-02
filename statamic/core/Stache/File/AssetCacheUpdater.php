<?php

namespace Statamic\Stache\File;

use Statamic\API\File;
use Statamic\API\Path;
use Statamic\API\Cache;
use Statamic\API\Config;
use Statamic\API\Folder;
use Statamic\FileCollection;
use Statamic\Contracts\Stache\AssetCache as AssetCacheContract;
use Statamic\Contracts\Stache\AssetCacheUpdater as AssetCacheUpdaterContract;

class AssetCacheUpdater implements AssetCacheUpdaterContract
{
    /**
     * @var \Statamic\Contracts\Stache\AssetCache
     */
    private $cache;

    /**
     * @var \Statamic\FileCollection
     */
    private $files;

    /**
     * @var \Statamic\Contracts\Stache\LocalizedAssetCacheService
     */
    private $localized_cacher;

    /**
     * @var array
     */
    private $timestamps;

    /**
     * @var array
     */
    private $cached_timestamps;

    /**
     * Load the cache
     *
     * @param \Statamic\Contracts\Stache\AssetCache $cache
     * @return \Statamic\Contracts\Stache\AssetCache
     */
    public function load(AssetCacheContract $cache)
    {
        foreach (Config::getLocales() as $locale) {
            $cache->setLocale($locale, $this->buildLocalCacheFromFile($locale));
        }

        return $cache;
    }

    /**
     * Update the cache
     *
     * @param \Statamic\Contracts\Stache\AssetCache $cache
     * @return \Statamic\Contracts\Stache\AssetCache
     */
    public function update(AssetCacheContract $cache)
    {
        $this->cache = $cache;

        // Grab all files so we have a basis for comparisons
        $this->files = $this->getAllFiles();

        // Populate the cache with some essentials
        $this->cache->setFiles($this->files);
        $this->cache->setTimestamps($this->getTimestampsFromFile());

        // Get deleted and modified files
        $this->cache->setDeletedFiles($deleted_files = $this->getDeletedFiles());
        $this->cache->setModifiedFiles($modified_files = $this->getModifiedFiles());

        // No modifications? Just read the cache from disk
        if ($modified_files->isEmpty() && $deleted_files->isEmpty()) {
            return $this->load($this->cache);
        }

        // Making it this far means there are updates
        $this->cache->hasBeenUpdated(true);

        // Remove deleted files
        // todo
//        foreach ($deleted_files as $deleted_file) {
//            $this->cache->removeTimestamp($deleted_file);
//        }

        // Populate the cache with localized versions
        foreach (Config::getLocales() as $locale) {
            $localized_cache = $this->buildLocalCacheFromFile($locale);
            $this->localized_cacher = localized_asset_cache_service();
            $cache = $this->localized_cacher->update($localized_cache);
            $this->cache->setLocale($locale, $cache);
        }

        $this->writeCache();

        return $this->cache;
    }

    /**
     * @return \Statamic\FileCollection
     */
    private function getAllFiles()
    {
        if ($this->files) {
            return $this->files;
        }

        $files = new FileCollection(Folder::disk('storage')->getFilesRecursively('assets'));

        return $files->filterByExtension('yaml');
    }

    /**
     * @return array
     */
    private function getAllTimestamps()
    {
        if ($this->timestamps) {
            return $this->timestamps;
        }

        $timestamps = [];

        foreach ($this->getAllFiles() as $path) {
            $timestamps[$path] = File::disk('storage')->lastModified($path);
        }

        return $this->timestamps = $timestamps;
    }

    /**
     * @return \Statamic\FileCollection
     */
    private function getModifiedFiles()
    {
        // If there's no cache, then we already know whats modified. Everything.
        if (! $this->cached_timestamps) {
            return $this->getAllFiles();
        }

        $modified_files = [];

        // Get all the paths of files that have been modified
        foreach ($this->getAllTimestamps() as $file => $timestamp) {
            if (isset($this->getTimestampsFromFile()[$file])
                && $timestamp > $this->getTimestampsFromFile()[$file]
            ) {
                $modified_files[] = $file;
            };
        }

        // Get new files
        $new_files = array_diff(
            $this->getAllFiles()->all(),
            array_keys($this->getTimestampsFromFile())
        );

        return new FileCollection(array_merge($modified_files, $new_files));
    }

    /**
     * @return \Statamic\FileCollection
     */
    private function getDeletedFiles()
    {
        return new FileCollection(array_diff(
            array_keys($this->getTimestampsFromFile()),
            $this->getAllFiles()->all()
        ));
    }

    /**
     * @return array
     */
    private function getTimestampsFromFile()
    {
        if ($this->cached_timestamps) {
            return $this->cached_timestamps;
        }

        if ($timestamps = Cache::get('stache/assets/timestamps')) {
            return $this->cached_timestamps = unserialize($timestamps);
        }

        return [];
    }

    /**
     * Build up a localized cache object from file
     *
     * @param string $locale
     * @return \Statamic\Contracts\Stache\LocalizedAssetCache
     */
    private function buildLocalCacheFromFile($locale)
    {
        $cache = localized_asset_cache();

        $cache->setLocale($locale);

        if ($uuids = Cache::get('stache/assets/' . $locale . '/uuids')) {
            $cache->setUuids(unserialize($uuids));
        }

        if ($containers = unserialize(Cache::get('stache/assets/containers'))) {
            $assets = [];

            foreach ($containers as $uuid) {
                $assets[$uuid] = unserialize(Cache::get('stache/assets/'.$locale.'/containers/'.$uuid.'/data'));
            }

            $cache->setAssets($assets);
        }

        return $cache;
    }

    /**
     * Write the cache to file
     */
    private function writeCache()
    {
        Cache::put('stache/assets/timestamps', serialize($this->cache->getTimestamps()));

        foreach ($this->cache->getLocales() as $locale => $cache) {
            /** @var \Statamic\Stache\File\LocalizedAssetCache $cache */

            $folder = 'stache/assets/' . $locale;

            Cache::put($folder . '/uuids', serialize($cache->getUuids()));

            Cache::put('stache/assets/containers', serialize(array_keys($cache->getAssetContainers())));

            foreach ($cache->getAssets() as $container_uuid => $container) {
                Cache::put($folder . '/containers/' . $container_uuid . '/data', serialize($container));
            }
        }
    }
}
