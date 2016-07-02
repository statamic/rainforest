<?php

namespace Statamic\Stache\File;

use Statamic\API\URL;
use Statamic\API\Config;
use Statamic\Data\GlobalCollection;
use Statamic\Data\PageCollection;
use Statamic\Data\EntryCollection;
use Statamic\Contracts\Stache\ContentCache as ContentCacheContract;
use Statamic\Contracts\Stache\ContentCacheService as ContentCacheServiceContract;
use Statamic\Contracts\Stache\ContentCacheUpdater as ContentCacheUpdaterContract;

class ContentCacheService implements ContentCacheServiceContract
{
    /**
     * @var \Statamic\Contracts\Stache\ContentCache
     */
    private $cache;

    /**
     * @var \Statamic\Contracts\Stache\ContentCacheUpdater
     */
    private $updater;

    /**
     * @param \Statamic\Contracts\Stache\ContentCache        $cache
     * @param \Statamic\Contracts\Stache\ContentCacheUpdater $updater
     */
    public function __construct(ContentCacheContract $cache, ContentCacheUpdaterContract $updater)
    {
        $this->cache = $cache;
        $this->updater = $updater;
    }

    /**
     * @return mixed
     */
    public function update()
    {
        return $this->updater->update($this->cache);
    }

    /**
     * @return mixed
     */
    public function load()
    {
        return $this->updater->load($this->cache);
    }

    /**
     * Get content by UUID
     *
     * @param string      $uuid
     * @param string|null $locale
     * @return mixed
     */
    public function getUuid($uuid, $locale = null)
    {
        return $this->cache->getLocale($locale)->getContent($uuid);
    }

    /**
     * Get a page by URL
     *
     * @param string      $url
     * @param string|null $locale
     * @return \Statamic\Data\Page
     */
    public function getPage($url, $locale = null)
    {
        if ($locale !== Config::getDefaultLocale()) {
            $url = URL::unlocalize($url, $locale);
        }

        $uuid = $this->cache->getLocale(Config::getDefaultLocale())->getPageUuid($url);

        return $this->cache->getLocale($locale)->getPage($uuid);
    }

    /**
     * Get entry by collection and slug
     *
     * @param string      $slug
     * @param string      $collection
     * @param string|null $locale
     * @return \Statamic\Data\Entry
     */
    public function getEntry($slug, $collection, $locale = null)
    {
        $uuid = $this->cache->getLocale(default_locale())->getEntryUuid($collection . '/' . $slug);

        return $this->cache->getLocale($locale)->getEntry($uuid, $collection);
    }

    /**
     * Get taxonomy by group and slug
     *
     * @param string      $slug
     * @param string      $taxonomy
     * @param string|null $locale
     * @return \Statamic\Contracts\Data\Taxonomies\Term
     */
    public function getTaxonomyTerm($slug, $taxonomy, $locale = null)
    {
        $local_cache = $this->cache->getLocale($locale);

        $uuid = $local_cache->getTaxonomyUuid($taxonomy . '/' . $slug);

        // If the UUID couldn't be found, we're dealing with a localized slug
        if (! $uuid) {
            $uuids = array_flip($local_cache->getLocalizedSlugs()['taxonomies']);
            $uuid = array_get($uuids, $slug);
        }

        return $local_cache->getTaxonomyTerm($uuid, $taxonomy);
    }

    /**
     * @param string|null $locale
     * @return \Statamic\Data\PageCollection
     */
    public function getPages($locale = null)
    {
        return collect_pages($this->cache->getLocale($locale)->getPages());
    }

    /**
     * @param string|null $locale
     * @return \Statamic\Contracts\Data\Pages\PageFolder[]
     */
    public function getPageFolders($locale = null)
    {
        return $this->cache->getLocale($locale)->getPageFolders();
    }

    /**
     * @param string      $path
     * @param string|null $locale
     * @return \Statamic\Contracts\Data\Pages\PageFolder
     */
    public function getPageFolder($path, $locale = null)
    {
        return $this->cache->getLocale($locale)->getPageFolder($path);
    }

    /**
     * @param string|null $locale
     * @return \Statamic\Data\EntryCollection[]
     */
    public function getEntryCollections($locale = null)
    {
        $collections = [];

        foreach ($this->cache->getLocale($locale)->getEntries() as $name => $collection) {
            $collections[$name] = collect_entries($collection);
        }

        return $collections;
    }

    /**
     * @param string $collection
     * @param null   $locale
     * @return \Statamic\Data\EntryCollection
     */
    public function getEntryCollection($collection, $locale = null)
    {
        return array_get($this->getEntryCollections($locale), $collection);
    }

    /**
     * @return array
     */
    public function getStructure()
    {
        return $this->cache->getLocale(Config::getDefaultLocale())->getStructure();
    }

    /**
     * @param string|null $locale
     * @return \Statamic\Data\Taxonomies\TermCollection[]
     */
    public function getTaxonomyTerms($locale = null)
    {
        return $this->cache->getLocale($locale)->getTaxonomyTerms();
    }

    /**
     * @param string|null $locale
     * @return \Statamic\Contracts\Data\Taxonomies\Taxonomy[]
     */
    public function getTaxonomies($locale = null)
    {
        return $this->cache->getLocale($locale)->getTaxonomies();
    }

    /**
     * @param string       $taxonomy
     * @param string|null  $locale
     * @return \Statamic\Contracts\Data\Taxonomies\Taxonomy
     */
    public function getTaxonomy($taxonomy, $locale = null)
    {
        return $this->cache->getLocale($locale)->getTaxonomy($taxonomy);
    }

    /**
     * @param string|null $locale
     * @return array
     */
    public function getLocalizedUrls($locale = null)
    {
        return $this->cache->getLocale($locale)->getLocalizedUrls();
    }

    /**
     * @param string|null $locale
     * @return array
     */
    public function getLocalizedSlugs($locale = null)
    {
        return $this->cache->getLocale($locale)->getLocalizedSlugs();
    }

    /**
     * @param string|null $locale
     * @return \Statamic\Contracts\Data\CollectionFolder[]
     */
    public function getCollections($locale = null)
    {
        return $this->cache->getLocale($locale)->getCollections();
    }

    /**
     * @param string       $collection
     * @param string|null  $locale
     * @return \Statamic\Contracts\Data\CollectionFolder
     */
    public function getCollection($collection, $locale = null)
    {
        return $this->cache->getLocale($locale)->getCollection($collection);
    }

    /**
     * Get a global by slug
     *
     * @param string      $slug
     * @param string|null $locale
     * @return \Statamic\Data\GlobalContent
     */
    public function getGlobal($slug, $locale = null)
    {
        $uuid = $this->cache->getLocale(Config::getDefaultLocale())->getGlobalUuid($slug);

        return $this->cache->getLocale($locale)->getGlobal($uuid);
    }

    /**
     * @param string|null $locale
     * @return \Statamic\Data\GlobalCollection
     */
    public function getGlobals($locale = null)
    {
        return collect_globals($this->cache->getLocale($locale)->getGlobals());
    }
}
