<?php

namespace Statamic\Contracts\Stache;

interface ContentCacheService
{
    /**
     * Update the cache
     *
     * @return \Statamic\Contracts\Stache\ContentCache
     */
    public function update();

    /**
     * Load the cache
     *
     * @return \Statamic\Contracts\Stache\ContentCache
     */
    public function load();

    /**
     * Get content by UUID
     *
     * @param string      $uuid
     * @param string|null $locale
     * @return \Statamic\Data\Content
     */
    public function getUuid($uuid, $locale = null);

    /**
     * Get a page by URL
     *
     * @param string      $url
     * @param string|null $locale
     * @return \Statamic\Data\Page
     */
    public function getPage($url, $locale = null);

    /**
     * Get entry by collection and slug
     *
     * @param string      $slug
     * @param string      $collection
     * @param string|null $locale
     * @return \Statamic\Data\Entry
     */
    public function getEntry($slug, $collection, $locale = null);

    /**
     * Get term by taxonomy and slug
     *
     * @param string      $slug
     * @param string      $taxonomy
     * @param string|null $locale
     * @return \Statamic\Contracts\Data\Taxonomies\Term
     */
    public function getTaxonomyTerm($slug, $taxonomy, $locale = null);

    /**
     * Get a global by slug
     *
     * @param string      $slug
     * @param string|null $locale
     * @return \Statamic\Data\GlobalContent
     */
    public function getGlobal($slug, $locale = null);

    /**
     * @param string|null $locale
     * @return \Statamic\Data\PageCollection
     */
    public function getPages($locale = null);

    /**
     * @param string|null $locale
     * @return \Statamic\Contracts\Data\Pages\PageFolder[]
     */
    public function getPageFolders($locale = null);

    /**
     * @param string      $path
     * @param string|null $locale
     * @return \Statamic\Contracts\Data\Pages\PageFolder
     */
    public function getPageFolder($path, $locale = null);

    /**
     * @param string|null $locale
     * @return \Statamic\Contracts\Data\CollectionFolder[]
     */
    public function getCollections($locale = null);

    /**
     * @param string      $collection
     * @param string|null $locale
     * @return \Statamic\Contracts\Data\CollectionFolder
     */
    public function getCollection($collection, $locale = null);

    /**
     * @param string|null $locale
     * @return \Statamic\Data\EntryCollection[]
     */
    public function getEntryCollections($locale = null);

    /**
     * @param string      $collection
     * @param string|null $locale
     * @return \Statamic\Data\EntryCollection
     */
    public function getEntryCollection($collection, $locale = null);

    /**
     * @param string|null $locale
     * @return \Statamic\Data\Taxonomies\TermCollection[]
     */
    public function getTaxonomyTerms($locale = null);

    /**
     * @param string|null $locale
     * @return \Statamic\Contracts\Data\Taxonomies\Taxonomy[]
     */
    public function getTaxonomies($locale = null);

    /**
     * @param string      $taxonomy
     * @param string|null $locale
     * @return \Statamic\Contracts\Data\Taxonomies\Taxonomy
     */
    public function getTaxonomy($taxonomy, $locale = null);

    /**
     * @param string|null $locale
     * @return \Statamic\Data\GlobalCollection
     */
    public function getGlobals($locale = null);

    /**
     * @return array
     */
    public function getStructure();

    /**
     * @param string|null $locale
     * @return array
     */
    public function getLocalizedUrls($locale = null);

    /**
     * @param string|null $locale
     * @return array
     */
    public function getLocalizedSlugs($locale = null);
}
