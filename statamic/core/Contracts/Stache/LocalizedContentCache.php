<?php

namespace Statamic\Contracts\Stache;

use Statamic\Contracts\Data\Pages\PageFolder;
use Statamic\Contracts\Data\Taxonomies\Taxonomy;
use Statamic\Contracts\Data\Globals\GlobalContent;
use Statamic\Contracts\Data\Pages\Page;
use Statamic\Contracts\Data\Entries\Entry;
use Statamic\Contracts\Data\Taxonomies\Term;
use Statamic\Contracts\Data\Entries\CollectionFolder;

interface LocalizedContentCache
{
    /**
     * Get the locale
     *
     * @return string
     */
    public function getLocale();

    /**
     * Get content
     *
     * @param string $uuid
     * @return \Statamic\Data\Content
     */
    public function getContent($uuid);

    /**
     * Set the locale
     *
     * @param string $locale
     */
    public function setLocale($locale);

    /**
     * Get all pages
     *
     * @return array
     */
    public function getPages();

    /**
     * Set all pages
     *
     * @param array $pages
     */
    public function setPages($pages);

    /**
     * Get a page
     *
     * @param string $uuid
     * @return \Statamic\Data\Page
     */
    public function getPage($uuid);

    /**
     * Set a page
     *
     * @param string              $uuid
     * @param \Statamic\Data\Page $page
     */
    public function setPage($uuid, Page $page);

    /**
     * Remove a page
     *
     * @param string $uuid
     */
    public function removePage($uuid);

    /**
     * Get all page folders
     *
     * @return array
     */
    public function getPageFolders();

    /**
     * Set all page folders
     *
     * @param array $folders
     */
    public function setPageFolders($folders);

    /**
     * Get a page folder
     *
     * @param string $path
     * @return \Statamic\Contracts\Data\Pages\PageFolder
     */
    public function getPageFolder($path);

    /**
     * Set a page folder
     *
     * @param string                                    $path
     * @param \Statamic\Contracts\Data\Pages\PageFolder $folder
     */
    public function setPageFolder($path, PageFolder $folder);

    /**
     * Remove a page folder
     *
     * @param string $path
     */
    public function removePageFolder($path);

    /**
     * Get all collections
     *
     * @return array
     */
    public function getCollections();

    /**
     * Set all collections
     *
     * @param array $collections
     */
    public function setCollections($collections);

    /**
     * Get a collection
     *
     * @param string $name
     * @return \Statamic\Contracts\Data\CollectionFolder
     */
    public function getCollection($name);

    /**
     * Set a collection
     *
     * @param string                                    $name
     * @param \Statamic\Contracts\Data\CollectionFolder $collection
     */
    public function setCollection($name, CollectionFolder $collection);

    /**
     * Remove a collection
     *
     * @param string $name
     */
    public function removeCollection($name);

    /**
     * Get all entries
     *
     * @return array
     */
    public function getEntries();

    /**
     * Set all entries
     *
     * @param array $entries
     */
    public function setEntries($entries);

    /**
     * @param string $uuid
     * @param string $collection
     * @return \Statamic\Data\Entry
     */
    public function getEntry($uuid, $collection);

    /**
     * Set an entry
     *
     * @param string               $uuid
     * @param string               $collection
     * @param \Statamic\Data\Entry $entry
     */
    public function setEntry($uuid, $collection, Entry $entry);

    /**
     * Remove an entry
     *
     * @param string $uuid
     * @param string $collection
     */
    public function removeEntry($uuid, $collection);

    /**
     * Get all taxonomy terms
     *
     * @return array
     */
    public function getTaxonomyTerms();

    /**
     * Set all taxonomy terms
     *
     * @param array $terms
     */
    public function setTaxonomyTerms($terms);

    /**
     * Get a taxonomy term
     *
     * @param string $id
     * @param string $taxonomy
     * @return \Statamic\Contracts\Data\Taxonomies\Term
     */
    public function getTaxonomyTerm($id, $taxonomy);

    /**
     * Set a taxonomy
     *
     * @param string                                   $id
     * @param string                                   $taxonomy
     * @param \Statamic\Contracts\Data\Taxonomies\Term $term
     */
    public function setTaxonomyTerm($id, $taxonomy, Term $term);

    /**
     * Remove a taxonomy term
     *
     * @param string $slug
     * @param string $taxonomy
     */
    public function removeTaxonomyTerm($slug, $taxonomy);

    /**
     * Get all taxonomies
     *
     * @return array
     */
    public function getTaxonomies();

    /**
     * Set all taxonomies
     *
     * @param array $taxonomies
     */
    public function setTaxonomies($taxonomies);

    /**
     * Get a taxonomy
     *
     * @param string $name
     * @return \Statamic\Contracts\Data\Taxonomies\Taxonomy
     */
    public function getTaxonomy($name);

    /**
     * Set a taxonomy
     *
     * @param string                                       $name
     * @param \Statamic\Contracts\Data\Taxonomies\Taxonomy $taxonomy
     */
    public function setTaxonomy($name, Taxonomy $taxonomy);

    /**
     * Remove a taxonomy
     *
     * @param string $name
     */
    public function removeTaxonomy($name);

    /**
     * Get all globals
     *
     * @return array
     */
    public function getGlobals();

    /**
     * Set all globals
     *
     * @param array $globals
     */
    public function setGlobals($globals);

    /**
     * Get a global
     *
     * @param string $uuid
     * @return \Statamic\Data\GlobalContent
     */
    public function getGlobal($uuid);

    /**
     * Set a global
     *
     * @param string                       $uuid
     * @param \Statamic\Data\GlobalContent $global
     */
    public function setGlobal($uuid, GlobalContent $global);

    /**
     * Remove a global
     *
     * @param string $uuid
     */
    public function removeGlobal($uuid);

    /**
     * Get a page from the structure
     *
     * @param string|null $url
     * @return array
     */
    public function getStructure($url = null);

    /**
     * Set a page in the structure
     *
     * @param string $url
     * @param array  $structure
     */
    public function setStructure($url, $structure);

    /**
     * Remove a page from the structure
     *
     * @param string $url
     */
    public function removeStructure($url);

    /**
     * Set the entire page structure
     *
     * @param array $structures
     */
    public function setStructures($structures);

    /**
     * Get all the UUIDs
     *
     * @return mixed
     */
    public function getUuids();

    /**
     * Set all the page UUIDs
     *
     * @param array $uuids
     * @return mixed
     */
    public function setPageUuids($uuids);

    /**
     * Set a page UUID
     *
     * @param string $uuid
     * @param string $reference
     */
    public function setPageUuid($uuid, $reference);

    /**
     * Remove a page UUID
     *
     * @param string $uuid
     */
    public function removePageUuid($uuid);

    /**
     * Get a page's uuid
     *
     * @param string $reference
     * @return string
     */
    public function getPageUuid($reference);

    /**
     * Get a reference value (eg. an ID or file path) by UUID
     *
     * @param string $uuid
     * @return string
     */
    public function getPageReferenceByUuid($uuid);

    /**
     * Set all the entry UUIDs
     *
     * @param array $uuids
     * @return mixed
     */
    public function setEntryUuids($uuids);

    /**
     * Set an entry UUID
     *
     * @param string $uuid
     * @param string $reference
     */
    public function setEntryUuid($uuid, $reference);

    /**
     * Remove an entry UUID
     *
     * @param string $uuid
     */
    public function removeEntryUuid($uuid);

    /**
     * Get an entry UUID by a reference value
     *
     * @param string $reference
     * @return string
     */
    public function getEntryUuid($reference);

    /**
     * Get a reference value (eg. an ID or file path) by UUID
     *
     * @param string $uuid
     * @return string
     */
    public function getEntryReferenceByUuid($uuid);

    /**
     * Set all the taxonomy UUIDs
     *
     * @param array $uuids
     * @return mixed
     */
    public function setTaxonomyUuids($uuids);

    /**
     * Set a taxonomy UUID
     *
     * @param string $uuid
     * @param string $reference
     */
    public function setTaxonomyUuid($uuid, $reference);

    /**
     * Remove a taxonomy UUID
     *
     * @param string $uuid
     */
    public function removeTaxonomyUuid($uuid);

    /**
     * Get a taxonomy UUID by a reference value
     *
     * @param string $reference
     * @return string
     */
    public function getTaxonomyUuid($reference);

    /**
     * Set all the global UUIDs
     *
     * @param array $uuids
     * @return mixed
     */
    public function setGlobalUuids($uuids);

    /**
     * Set a global UUID
     *
     * @param string $uuid
     * @param string $reference
     */
    public function setGlobalUuid($uuid, $reference);

    /**
     * Remove a global UUID
     *
     * @param string $uuid
     */
    public function removeGlobalUuid($uuid);

    /**
     * Get a global's uuid
     *
     * @param string $reference
     * @return string
     */
    public function getGlobalUuid($reference);

    /**
     * Get a reference value (eg. an ID or file path) by UUID
     *
     * @param string $uuid
     * @return string
     */
    public function getGlobalReferenceByUuid($uuid);

    /**
     * Get all paths
     *
     * @return array
     */
    public function getPaths();

    /**
     * Get all localized slugs
     *
     * @return array
     */
    public function getLocalizedSlugs();

    /**
     * Set all the page urls
     *
     * @param array $urls
     */
    public function setPageUrls($urls);

    /**
     * Set a page url
     *
     * @param string $url
     * @param string $reference
     */
    public function setPageUrl($url, $reference);

    /**
     * Remove a page URL
     *
     * @param string $url
     */
    public function removePageUrl($url);

    /**
     * Get a page URL by reference value
     *
     * @param string $reference
     * @return string
     */
    public function getPageUrl($reference);

    /**
     * Get a reference value by URL
     *
     * @param string $url
     * @return string
     */
    public function getPageReferenceByUrl($url);

    /**
     * Set all the localized page slugs
     *
     * @param array $slugs
     */
    public function setLocalizedPageSlugs($slugs);

    /**
     * Set a localized page slug
     *
     * @param string $uuid
     * @param string $slug
     */
    public function setLocalizedPageSlug($uuid, $slug);

    /**
     * Remove a localized page slug
     *
     * @param string $uuid
     */
    public function removeLocalizedPageSlug($uuid);

    /**
     * Set all the entry paths
     *
     * @param array $paths
     */
    public function setEntryPaths($paths);

    /**
     * Set an entry path
     *
     * @param string $path The collection/slug string
     * @param string $reference
     */
    public function setEntryPath($path, $reference);

    /**
     * Remove an entry path
     *
     * @param string $path The collection/slug string
     */
    public function removeEntryPath($path);

    /**
     * Get an entry path by reference value
     *
     * @param string $reference
     * @return string
     */
    public function getEntryPath($reference);

    /**
     * Get a reference value by path
     *
     * @param string $path The collection/slug string
     * @return string
     */
    public function getEntryReferenceByPath($path);

    /**
     * Set all the localized entry slugs
     *
     * @param array $slugs
     */
    public function setLocalizedEntrySlugs($slugs);

    /**
     * Set a localized entry slug
     *
     * @param string $uuid
     * @param string $slug
     */
    public function setLocalizedEntrySlug($uuid, $slug);

    /**
     * Remove a localized entry slug
     *
     * @param string $uuid
     */
    public function removeLocalizedEntrySlug($uuid);

    /**
     * Set all the taxonomy paths
     *
     * @param array $paths
     */
    public function setTaxonomyPaths($paths);

    /**
     * Set an taxonomy path
     *
     * @param string $path The group/slug string
     * @param string $reference
     */
    public function setTaxonomyPath($path, $reference);

    /**
     * Remove an taxonomy path
     *
     * @param string $path The group/slug string
     */
    public function removeTaxonomyPath($path);

    /**
     * Get an taxonomy path by reference value
     *
     * @param string $reference
     * @return string
     */
    public function getTaxonomyPath($reference);

    /**
     * Get a reference value by path
     *
     * @param string $path The group/slug string
     * @return string
     */
    public function getTaxonomyReferenceByPath($path);

    /**
     * Set all the localized taxonomy slugs
     *
     * @param array $slugs
     */
    public function setLocalizedTaxonomySlugs($slugs);

    /**
     * Set a localized taxonomy slug
     *
     * @param string $uuid
     * @param string $slug
     */
    public function setLocalizedTaxonomySlug($uuid, $slug);

    /**
     * Remove a localized taxonomy slug
     *
     * @param string $uuid
     */
    public function removeLocalizedTaxonomySlug($uuid);

    /**
     * Get all localized URLs
     *
     * @return array
     */
    public function getLocalizedUrls();

    /**
     * Set all the localized URLs
     *
     * @param array $urls
     */
    public function setLocalizedUrls($urls);

    /**
     * Set a localized URL
     *
     * @param string $url The localized URL
     * @param string $default The default URL
     */
    public function setLocalizedUrl($url, $default);

    /**
     * Remove a localized URL
     *
     * @param string $url
     */
    public function removeLocalizedUrl($url);

    /**
     * Set all the global paths
     *
     * @param array $paths
     */
    public function setGlobalPaths($paths);

    /**
     * Set a global path
     *
     * @param string $path The slug string
     * @param string $reference
     */
    public function setGlobalPath($path, $reference);

    /**
     * Remove a global path
     *
     * @param string $path The group/slug string
     */
    public function removeGlobalPath($path);

    /**
     * Get a global path by reference value
     *
     * @param string $reference
     * @return string
     */
    public function getGlobalPath($reference);
}
