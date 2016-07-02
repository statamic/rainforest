<?php

namespace Statamic\Stache\File;

use Statamic\API\File;
use Statamic\API\Path;
use Statamic\API\Cache;
use Statamic\API\Config;
use Statamic\API\Folder;
use Statamic\API\Helper;
use Statamic\API\Str;
use Statamic\Contracts\Stache\ContentCache as ContentCacheContract;
use Statamic\Contracts\Stache\ContentCacheUpdater as ContentCacheUpdaterContract;

class ContentCacheUpdater implements ContentCacheUpdaterContract
{
    /**
     * @var \League\Flysystem\Filesystem
     */
    private $filesystem;

    /**
     * @var \Statamic\Stache\File\ContentCache
     */
    private $cache;

    /**
     * @var array
     */
    private $cached_timestamps;

    /**
     * @var \Illuminate\Support\Collection
     */
    private $timestamps;

    /**
     * @var \Illuminate\Support\Collection
     */
    private $files;

    /**
     * @var \Statamic\Contracts\Stache\LocalizedContentCacheService
     */
    private $localized_cacher;

    /**
     * @param \Statamic\Contracts\Stache\ContentCache $cache
     * @return \Statamic\Stache\File\ContentCache
     */
    public function load(ContentCacheContract $cache)
    {
        foreach (Config::getLocales() as $locale) {
            $cache->setLocale($locale, $this->buildLocalCacheFromFile($locale));
        }

        return $cache;
    }

    /**
     * @param \Statamic\Contracts\Stache\ContentCache $cache
     * @return \Statamic\Contracts\Stache\ContentCache
     */
    public function update(ContentCacheContract $cache)
    {
        $this->filesystem = app('filesystem')->disk('content')->getDriver();

        $this->cache = $cache;

        // Since we're using singletons, everything is persisted throughout the request. We can check
        // that by looking at the files array. If the update method is being called a subsequent time,
        // it means we intend to force an update again, so we'll reset things here.
        if ($this->files) {
            $this->files = null;
        }

        // Grab all files so we have a basis for comparisons
        $all_files = $this->getAllFiles();
        $this->timestamps = $all_files->lists('timestamp', 'path');
        $this->files = $all_files->lists('path');

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
        foreach ($deleted_files as $deleted_file) {
            $this->cache->removeTimestamp($deleted_file);
        }

        // Populate the cache with localized versions
        foreach (Helper::ensureArray(Config::getLocales()) as $locale) {
            $localized_cache = $this->buildLocalCacheFromFile($locale);
            $this->localized_cacher = localized_content_cache_service();
            $cache = $this->localized_cacher->update($localized_cache);
            $this->cache->setLocale($locale, $cache);
        }

        $this->writeCache();

        return $this->cache;
    }

    /**
     * Build up a localized cache object from file
     *
     * @param string $locale
     * @return \Statamic\Contracts\Stache\LocalizedContentCache
     */
    private function buildLocalCacheFromFile($locale)
    {
        $cache = localized_content_cache();

        $cache->setLocale($locale);

        if ($pages = Cache::get('stache/content/' . $locale . '/pages')) {
            $cache->setPages(unserialize($pages));
        }

        if ($page_folders = Cache::get('stache/content/' . $locale . '/page_folders')) {
            $cache->setPageFolders(unserialize($page_folders));
        }

        if ($collections = Cache::get('stache/content/' . $locale . '/collection_folders')) {
            $cache->setCollections(unserialize($collections));
        }

        $entries = [];
        foreach ($cache->getCollections() as $collection_name => $folder) {
            $collection = unserialize(Cache::get('stache/content/' . $locale . '/collections/' . $collection_name));
            $collection = $collection ?: []; // account for empty collections
            $entries[basename($collection_name)] = $collection;
        }
        $cache->setEntries($entries);

        if ($taxonomy_groups = Cache::get('stache/content/' . $locale . '/taxonomies')) {
            $cache->setTaxonomies(unserialize($taxonomy_groups));
        }

        $taxonomies = [];
        foreach ($cache->getTaxonomies() as $taxonomy_name => $folder) {
            $terms = unserialize(Cache::get('stache/content/' . $locale . '/terms/' . $taxonomy_name));
            $terms = $terms ?: []; // account for empty taxonomies
            $taxonomies[basename($taxonomy_name)] = $terms;
        }
        $cache->setTaxonomyTerms($taxonomies);

        if ($structure = Cache::get('stache/content/' . $locale . '/structure')) {
            $cache->setStructures(unserialize($structure));
        }

        if ($globals = Cache::get('stache/content/' . $locale . '/globals')) {
            $cache->setGlobals(unserialize($globals));
        }

        if ($localized_urls = Cache::get('stache/content/' . $locale . '/localized_urls')) {
            $cache->setLocalizedUrls(unserialize($localized_urls));
        }

        if ($uuids = Cache::get('stache/content/' . $locale . '/uuids')) {
            $uuids = unserialize($uuids);
            $cache->setPageUuids(array_get($uuids, 'pages', []));
            $cache->setEntryUuids(array_get($uuids, 'entries', []));
            $cache->setTaxonomyUuids(array_get($uuids, 'taxonomies', []));
            $cache->setGlobalUuids(array_get($uuids, 'globals', []));
        }

        if ($paths = Cache::get('stache/content/' . $locale . '/paths')) {
            $paths = unserialize($paths);
            $cache->setPageUrls(array_get($paths, 'pages', []));
            $cache->setEntryPaths(array_get($paths, 'entries', []));
            $cache->setTaxonomyPaths(array_get($paths, 'taxonomies', []));
            $cache->setGlobalPaths(array_get($paths, 'globals', []));
        }

        if ($localized_slugs = Cache::get('stache/content/' . $locale . '/localized_slugs')) {
            $localized_slugs = unserialize($localized_slugs);
            $cache->setLocalizedPageSlugs(array_get($localized_slugs, 'pages', []));
            $cache->setLocalizedEntrySlugs(array_get($localized_slugs, 'entries', []));
            $cache->setLocalizedTaxonomySlugs(array_get($localized_slugs, 'taxonomies', []));
        }

        return $cache;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    private function getAllFiles()
    {
        $files = collect($this->filesystem->listContents('/', true));

        return $files->filter(function($file) {
            // We only want files, not directories
            if ($file['type'] !== 'file') {
                return false;
            }

            // We only want content files
            if (! in_array($file['extension'], ['md', 'markdown', 'textile', 'txt', 'html', 'yaml'])) {
                return false;
            }

            // For globals, we only want yaml files
            if (Str::startsWith($file['path'], 'globals') && $file['extension'] !== 'yaml') {
                return false;
            }

            return true;
        });
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    private function getModifiedFiles()
    {
        $modified_files = [];

        // If there's no cache, then we already know whats modified. Everything.
        if (! $this->cached_timestamps) {
            return $this->files;
        }

        // Get all the paths of files that have been modified
        foreach ($this->timestamps as $file => $timestamp) {
            if (isset($this->getTimestampsFromFile()[$file])
                && $timestamp > $this->getTimestampsFromFile()[$file]
            ) {
                $modified_files[] = $file;
            };
        }

        // Get new files
        $new_files = array_diff(
            $this->files->all(),
            array_keys($this->getTimestampsFromFile())
        );

        return collect(array_merge($modified_files, $new_files));
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    private function getDeletedFiles()
    {
        return collect(array_diff(
            array_keys($this->getTimestampsFromFile()),
            $this->files->all()
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

        if ($timestamps = Cache::get('stache/content/timestamps')) {
            return $this->cached_timestamps = unserialize($timestamps);
        }

        return [];
    }

    /**
     * Write the cache to file
     */
    private function writeCache()
    {
        Cache::put('stache/content/timestamps', serialize($this->cache->getTimestamps()));

        foreach ($this->cache->getLocales() as $locale => $cache) {
            $folder = 'stache/content/' . $locale;

            Cache::put($folder . '/pages', serialize($cache->getPages()));
            Cache::put($folder . '/page_folders', serialize($cache->getPageFolders()));
            Cache::put($folder . '/structure', serialize($cache->getStructure()));
            Cache::put($folder . '/uuids', serialize($cache->getUuids()));
            Cache::put($folder . '/paths', serialize($cache->getPaths()));
            Cache::put($folder . '/localized_slugs', serialize($cache->getLocalizedSlugs()));
            Cache::put($folder . '/localized_urls', serialize($cache->getLocalizedUrls()));
            Cache::put($folder . '/globals', serialize($cache->getGlobals()));

            Cache::put($folder . '/collection_folders', serialize($cache->getCollections()));

            foreach ($cache->getEntries() as $entry_collection => $entries) {
                Cache::put($folder . '/collections/' . $entry_collection, serialize($entries));
            }

            Cache::put($folder . '/taxonomies', serialize($cache->getTaxonomies()));

            foreach ($cache->getTaxonomyTerms() as $group => $taxonomies) {
                Cache::put($folder . '/terms/' . $group, serialize($taxonomies));
            }
        }
    }
}
