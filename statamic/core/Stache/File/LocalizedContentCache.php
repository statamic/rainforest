<?php

namespace Statamic\Stache\File;

use Statamic\Contracts\Data\Globals\GlobalContent;
use Statamic\Contracts\Data\Pages\Page;
use Statamic\Contracts\Data\Entries\Entry;
use Statamic\Contracts\Data\Pages\PageFolder;
use Statamic\Contracts\Data\Taxonomies\Term;
use Statamic\Contracts\Data\Taxonomies\Taxonomy;
use Statamic\Contracts\Data\Entries\CollectionFolder;
use Statamic\Contracts\Stache\LocalizedContentCache as LocalizedContentCacheContract;

class LocalizedContentCache implements LocalizedContentCacheContract
{
    /**
     * The locale of this cache
     *
     * @var string
     */
    private $locale;

    /**
     * All content (pages, entries and taxonomies) combined in one array
     *
     * @var array
     */
    private $data;

    /**
     * "URL to Page" mapping of content objects
     *
     * @var array
     */
    private $pages = [];

    /**
     * Page Folders
     *
     * @var array
     */
    private $page_folders = [];

    /**
     * Collections
     *
     * @var array
     */
    private $collections = [];

    /**
     * "Slug to Entry" mapping of content objects, sorted into collections
     *
     * @var array
     */
    private $entries = [];

    /**
     * Taxonomy groups
     *
     * @var array
     */
    private $taxonomy_groups = [];

    /**
     * "Slug to Taxonomy" mapping of content objects, sorted into groups
     *
     * @var array
     */
    private $taxonomy_terms = [];

    /**
     * "Slug to Globals" mapping of content objects
     *
     * @var array
     */
    private $globals = [];

    /**
     * "URL to structure array" mapping of structure data
     *
     * @var array
     */
    private $structure = [];

    /**
     * "UUID to file path" mappings, sorted into types
     *
     * @var array
     */
    private $uuids = [];

    /**
     * "Path identifiers to file path" mappings, sorted into types.
     *
     * /url for pages
     * collection/slug for entries
     * group/slug for taxonomies
     * slug for globals
     *
     * @var array
     */
    private $paths = [];

    /**
     * "UUID to slug" mappings, sorted into types
     *
     * @var array
     */
    private $localized_slugs = [];

    /**
     * "Localized URL to default URL" mappings
     *
     * @var array
     */
    private $localized_urls = [];

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @param string $uuid
     * @return \Statamic\Data\Content
     */
    public function getContent($uuid)
    {
        if ($this->data) {
            $data = $this->data;

        } else {
            $data = $this->pages;

            $data = array_merge($data, $this->globals);

            foreach ($this->entries as $collection) {
                $data = array_merge($data, $collection);
            }

            foreach ($this->taxonomy_terms as $taxonomy) {
                $data = array_merge($data, $taxonomy);
            }

            $this->data = $data;
        }

        return array_get($data, $uuid);
    }

    /**
     * @return array
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * @param string $uuid
     * @return mixed
     */
    public function getPage($uuid)
    {
        if (! $uuid) {
            return null;
        }

        return array_get($this->pages, $uuid);
    }

    /**
     * @param string              $uuid
     * @param \Statamic\Data\Page $page
     */
    public function setPage($uuid, Page $page)
    {
        $this->pages[$uuid] = $page;
    }

    /**
     * @param array $pages
     */
    public function setPages($pages)
    {
        $this->pages = $pages;
    }

    /**
     * @param string $uuid
     */
    public function removePage($uuid)
    {
        unset($this->pages[$uuid]);
    }

    /**
     * Get all page folders
     *
     * @return array
     */
    public function getPageFolders()
    {
        return $this->page_folders;
    }

    /**
     * Set all page folders
     *
     * @param array $folders
     */
    public function setPageFolders($folders)
    {
        $this->page_folders = $folders;
    }

    /**
     * Get a page folder
     *
     * @param string $path
     * @return \Statamic\Contracts\Data\Pages\PageFolder
     */
    public function getPageFolder($path)
    {
        return array_get($this->page_folders, $path);
    }

    /**
     * Set a page folder
     *
     * @param string                                    $path
     * @param \Statamic\Contracts\Data\Pages\PageFolder $folder
     */
    public function setPageFolder($path, PageFolder $folder)
    {
        $this->page_folders[$path] = $folder;
    }

    /**
     * Remove a page folder
     *
     * @param string $path
     */
    public function removePageFolder($path)
    {
        unset($this->page_folders[$path]);
    }

    /**
     * Get all collections
     *
     * @return array
     */
    public function getCollections()
    {
        return $this->collections;
    }

    /**
     * Set all collections
     *
     * @param array $collections
     */
    public function setCollections($collections)
    {
        $this->collections = $collections;
    }

    /**
     * Get a collection
     *
     * @param string $name
     * @return \Statamic\Contracts\Data\CollectionFolder
     */
    public function getCollection($name)
    {
        return array_get($this->collections, $name);
    }

    /**
     * Set a collection
     *
     * @param string                                    $name
     * @param \Statamic\Contracts\Data\CollectionFolder $collection
     */
    public function setCollection($name, CollectionFolder $collection)
    {
        $this->collections[$name] = $collection;
    }

    /**
     * Remove a collection
     *
     * @param string $name
     */
    public function removeCollection($name)
    {
        unset($this->collections[$name]);
    }

    /**
     * @return array
     */
    public function getEntries()
    {
        return $this->entries;
    }

    /**
     * @param array $entries
     */
    public function setEntries($entries)
    {
        $this->entries = $entries;
    }

    /**
     * @param string $uuid
     * @param string $collection
     * @return mixed
     */
    public function getEntry($uuid, $collection)
    {
        $collection = array_get($this->entries, $collection);

        return array_get($collection, $uuid);
    }

    /**
     * @param string               $uuid
     * @param string               $collection
     * @param \Statamic\Data\Entry $entry
     */
    public function setEntry($uuid, $collection, Entry $entry)
    {
        $this->entries[$collection][$uuid] = $entry;
    }

    /**
     * @param string $uuid
     * @param string $collection
     */
    public function removeEntry($uuid, $collection)
    {
        unset($this->entries[$collection][$uuid]);
    }

    /**
     * @return array
     */
    public function getTaxonomyTerms()
    {
        return $this->taxonomy_terms;
    }

    /**
     * @param array $taxonomies
     */
    public function setTaxonomyTerms($taxonomies)
    {
        $this->taxonomy_terms = $taxonomies;
    }


    /**
     * Get all taxonomies
     *
     * @return array
     */
    public function getTaxonomies()
    {
        return $this->taxonomy_groups;
    }

    /**
     * Set all taxonomies
     *
     * @param array $taxonomies
     */
    public function setTaxonomies($taxonomies)
    {
        $this->taxonomy_groups = $taxonomies;
    }

    /**
     * Get a taxonomy
     *
     * @param string $name
     * @return \Statamic\Contracts\Data\Taxonomies\Taxonomy
     */
    public function getTaxonomy($name)
    {
        return array_get($this->taxonomy_groups, $name);
    }

    /**
     * Set a taxonomy
     *
     * @param string                                       $name
     * @param \Statamic\Contracts\Data\Taxonomies\Taxonomy $taxonomy
     */
    public function setTaxonomy($name, Taxonomy $taxonomy)
    {
        $this->taxonomy_groups[$name] = $taxonomy;
    }

    /**
     * Remove a taxonomy
     *
     * @param string $name
     */
    public function removeTaxonomy($name)
    {
        unset($this->taxonomy_groups[$name]);
    }

    /**
     * @param string $id
     * @param string $taxonomy
     * @return \Statamic\Contracts\Data\Taxonomies\Term
     */
    public function getTaxonomyTerm($id, $taxonomy)
    {
        if (! $id) {
            return null;
        }

        return array_get($this->taxonomy_terms[$taxonomy], $id);
    }

    /**
     * @param string                  $id
     * @param string                  $taxonomy
     * @param \Statamic\Contracts\Data\Taxonomies\Term $term
     */
    public function setTaxonomyTerm($id, $taxonomy, Term $term)
    {
        $this->taxonomy_terms[$taxonomy][$id] = $term;
    }

    /**
     * @param string $id
     * @param string $taxonomy
     */
    public function removeTaxonomyTerm($id, $taxonomy)
    {
        unset($this->taxonomy_terms[$taxonomy][$id]);
    }

    /**
     * @param null $url
     * @return array|mixed
     */
    public function getStructure($url = null)
    {
        if ($url) {
            return array_get($this->structure, $url);
        }

        return $this->structure;
    }

    /**
     * @param string $url
     * @param array  $structure
     */
    public function setStructure($url, $structure)
    {
        $this->structure[$url] = $structure;
    }

    /**
     * @param array $structures
     */
    public function setStructures($structures)
    {
        $this->structure = $structures;
    }

    /**
     * @param string $url
     */
    public function removeStructure($url)
    {
        unset($this->structure[$url]);
    }

    /**
     * Get all the UUIDs
     *
     * @return mixed
     */
    public function getUuids()
    {
        return $this->uuids;
    }

    /**
     * Set all the page UUIDs
     *
     * @param array $uuids
     * @return mixed
     */
    public function setPageUuids($uuids)
    {
        $this->uuids['pages'] = $uuids;
    }

    /**
     * Set a page UUID
     *
     * @param string $uuid
     * @param string $reference The file path
     */
    public function setPageUuid($uuid, $reference)
    {
        $this->uuids['pages'][$uuid] = $reference;
    }

    /**
     * Remove a page UUID
     *
     * @param string $uuid
     */
    public function removePageUuid($uuid)
    {
        unset($this->uuids['pages'][$uuid]);
    }

    /**
     * Get a page's uuid
     *
     * @param string $reference The file path
     * @return string
     */
    public function getPageUuid($reference)
    {
        return array_get(array_flip($this->uuids['pages']), $reference);
    }

    /**
     * Get a reference value (eg. an ID or file path) by UUID
     *
     * @param string $uuid
     * @return string
     */
    public function getPageReferenceByUuid($uuid)
    {
        return array_get($this->uuids['pages'], $uuid);
    }

    /**
     * Set all the entry UUIDs
     *
     * @param array $uuids
     * @return mixed
     */
    public function setEntryUuids($uuids)
    {
        $this->uuids['entries'] = $uuids;
    }

    /**
     * Set an entry UUID
     *
     * @param string $uuid
     * @param string $reference The file path
     */
    public function setEntryUuid($uuid, $reference)
    {
        $this->uuids['entries'][$uuid] = $reference;
    }

    /**
     * Remove an entry UUID
     *
     * @param string $uuid
     */
    public function removeEntryUuid($uuid)
    {
        unset($this->uuids['entries'][$uuid]);
    }

    /**
     * Get an entry UUID by a reference value
     *
     * @param string $reference
     * @return string
     */
    public function getEntryUuid($reference)
    {
        return array_get(array_flip($this->uuids['entries']), $reference);
    }

    /**
     * Get a reference value (eg. an ID or file path) by UUID
     *
     * @param string $uuid
     * @return string
     */
    public function getEntryReferenceByUuid($uuid)
    {
        return array_get($this->uuids['entries'], $uuid);
    }

    /**
     * Set all the taxonomy UUIDs
     *
     * @param array $uuids
     * @return mixed
     */
    public function setTaxonomyUuids($uuids)
    {
        $this->uuids['taxonomies'] = $uuids;
    }

    /**
     * Set a taxonomy UUID
     *
     * @param string $uuid
     * @param string $reference The file path
     */
    public function setTaxonomyUuid($uuid, $reference)
    {
        $this->uuids['taxonomies'][$uuid] = $reference;
    }

    /**
     * Remove a taxonomy UUID
     *
     * @param string $uuid
     */
    public function removeTaxonomyUuid($uuid)
    {
        unset($this->uuids['taxonomies'][$uuid]);
    }

    /**
     * Get a taxonomy UUID by a reference value
     *
     * @param string $reference
     * @return string
     */
    public function getTaxonomyUuid($reference)
    {
        return array_get(array_flip($this->uuids['taxonomies']), $reference);
    }

    /**
     * Get a reference value (eg. an ID or file path) by UUID
     *
     * @param string $uuid
     * @return string
     */
    public function getTaxonomyReferenceByUuid($uuid)
    {
        return array_get($this->uuids['taxonomies'], $uuid);
    }

    /**
     * Set all the page urls
     *
     * @param array $urls
     */
    public function setPageUrls($urls)
    {
        $this->paths['pages'] = $urls;
    }

    /**
     * Set a page url
     *
     * @param string $url
     * @param string $reference The file path
     */
    public function setPageUrl($url, $reference)
    {
        $this->paths['pages'][$url] = $reference;
    }

    /**
     * Remove a page URL
     *
     * @param string $url
     */
    public function removePageUrl($url)
    {
        unset($this->paths['pages'][$url]);
    }

    /**
     * Get a page URL by reference value
     *
     * @param string $reference The file path
     * @return string
     */
    public function getPageUrl($reference)
    {
        return array_get(array_flip($this->paths['pages']), $reference);
    }

    /**
     * Get a reference value by URL
     *
     * @param string $url
     * @return string
     */
    public function getPageReferenceByUrl($url)
    {
        return array_get($this->paths['pages'], $url);
    }

    /**
     * Set all the entry paths
     *
     * @param array $paths
     */
    public function setEntryPaths($paths)
    {
        $this->paths['entries'] = $paths;
    }

    /**
     * Set an entry path
     *
     * @param string $path      The collection/slug string
     * @param string $reference The file path
     */
    public function setEntryPath($path, $reference)
    {
        $this->paths['entries'][$path] = $reference;
    }

    /**
     * Remove an entry path
     *
     * @param string $path The collection/slug string
     */
    public function removeEntryPath($path)
    {
        unset($this->paths['entries'][$path]);
    }

    /**
     * Get an entry path by reference value
     *
     * @param string $reference The file path
     * @return string
     */
    public function getEntryPath($reference)
    {
        return array_get(array_flip($this->paths['entries']), $reference);
    }

    /**
     * Get a reference value by path
     *
     * @param string $path The collection/slug string
     * @return string
     */
    public function getEntryReferenceByPath($path)
    {
        return array_get($this->paths['entries'], $path);
    }

    /**
     * Set all the taxonomy paths
     *
     * @param array $paths
     */
    public function setTaxonomyPaths($paths)
    {
        $this->paths['taxonomies'] = $paths;
    }

    /**
     * Set an taxonomy path
     *
     * @param string $path      The group/slug string
     * @param string $reference The file path
     */
    public function setTaxonomyPath($path, $reference)
    {
        $this->paths['taxonomies'][$path] = $reference;
    }

    /**
     * Remove an taxonomy path
     *
     * @param string $path The group/slug string
     */
    public function removeTaxonomyPath($path)
    {
        unset($this->paths['taxonomies'][$path]);
    }

    /**
     * Get an taxonomy path by reference value
     *
     * @param string $reference The file path
     * @return string
     */
    public function getTaxonomyPath($reference)
    {
        return array_get(array_flip($this->paths['taxonomies']), $reference);
    }

    /**
     * Get a reference value by path
     *
     * @param string $path The group/slug string
     * @return string
     */
    public function getTaxonomyReferenceByPath($path)
    {
        return array_get($this->paths['taxonomies'], $path);
    }

    /**
     * Set all the localized page slugs
     *
     * @param array $slugs
     */
    public function setLocalizedPageSlugs($slugs)
    {
        $this->localized_slugs['pages'] = $slugs;
    }

    /**
     * Set a localized page slug
     *
     * @param string $uuid
     * @param string $slug
     */
    public function setLocalizedPageSlug($uuid, $slug)
    {
        $this->localized_slugs['pages'][$uuid] = $slug;
    }

    /**
     * Remove a localized page slug
     *
     * @param string $uuid
     */
    public function removeLocalizedPageSlug($uuid)
    {
        unset($this->localized_slugs['pages'][$uuid]);
    }

    /**
     * Set all the localized entry slugs
     *
     * @param array $slugs
     */
    public function setLocalizedEntrySlugs($slugs)
    {
        $this->localized_slugs['entries'] = $slugs;
    }

    /**
     * Set a localized entry slug
     *
     * @param string $uuid
     * @param string $slug
     */
    public function setLocalizedEntrySlug($uuid, $slug)
    {
        $this->localized_slugs['entries'][$uuid] = $slug;
    }

    /**
     * Remove a localized entry slug
     *
     * @param string $uuid
     */
    public function removeLocalizedEntrySlug($uuid)
    {
        unset($this->localized_slugs['entries'][$uuid]);
    }

    /**
     * Set all the localized taxonomy slugs
     *
     * @param array $slugs
     */
    public function setLocalizedTaxonomySlugs($slugs)
    {
        $this->localized_slugs['taxonomies'] = $slugs;
    }

    /**
     * Set a localized taxonomy slug
     *
     * @param string $uuid
     * @param string $slug
     */
    public function setLocalizedTaxonomySlug($uuid, $slug)
    {
        $this->localized_slugs['taxonomies'][$uuid] = $slug;
    }

    /**
     * Remove a localized taxonomy slug
     *
     * @param string $uuid
     */
    public function removeLocalizedTaxonomySlug($uuid)
    {
        unset($this->localized_slugs['taxonomies'][$uuid]);
    }

    /**
     * Get all paths
     *
     * @return array
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * Get all localized slugs
     *
     * @return array
     */
    public function getLocalizedSlugs()
    {
        return $this->localized_slugs;
    }

    /**
     * Set all the localized URLs
     *
     * @param array $urls
     */
    public function setLocalizedUrls($urls)
    {
        $this->localized_urls = $urls;
    }

    /**
     * Get all localized URLs
     *
     * @return array
     */
    public function getLocalizedUrls()
    {
        return $this->localized_urls;
    }

    /**
     * Set a localized URL
     *
     * @param string $url     The localized URL
     * @param string $default The default URL
     */
    public function setLocalizedUrl($url, $default)
    {
        $this->localized_urls[$url] = $default;
    }

    /**
     * Remove a localized URL
     *
     * @param string $url
     */
    public function removeLocalizedUrl($url)
    {
        unset($this->localized_urls[$url]);
    }

    /**
     * Get all globals
     *
     * @return array
     */
    public function getGlobals()
    {
        return $this->globals;
    }

    /**
     * Set all globals
     *
     * @param array $globals
     */
    public function setGlobals($globals)
    {
        $this->globals = $globals;
    }

    /**
     * Get a global
     *
     * @param string $uuid
     * @return \Statamic\Data\GlobalContent
     */
    public function getGlobal($uuid)
    {
        if (! $uuid) {
            return null;
        }

        return array_get($this->globals, $uuid);
    }

    /**
     * Set a global
     *
     * @param string                       $uuid
     * @param \Statamic\Data\GlobalContent $global
     */
    public function setGlobal($uuid, GlobalContent $global)
    {
        $this->globals[$uuid] = $global;
    }

    /**
     * Remove a global
     *
     * @param string $uuid
     */
    public function removeGlobal($uuid)
    {
        unset($this->globals[$uuid]);
    }

    /**
     * Set all the global UUIDs
     *
     * @param array $uuids
     * @return mixed
     */
    public function setGlobalUuids($uuids)
    {
        $this->uuids['globals'] = $uuids;
    }

    /**
     * Set a global UUID
     *
     * @param string $uuid
     * @param string $reference
     */
    public function setGlobalUuid($uuid, $reference)
    {
        $this->uuids['globals'][$uuid] = $reference;
    }

    /**
     * Remove a global UUID
     *
     * @param string $uuid
     */
    public function removeGlobalUuid($uuid)
    {
        unset($this->uuids['globals'][$uuid]);
    }

    /**
     * Get a global's uuid
     *
     * @param string $reference
     * @return string
     */
    public function getGlobalUuid($reference)
    {
        return array_get(array_flip($this->uuids['globals']), $reference);
    }

    /**
     * Get a reference value (eg. an ID or file path) by UUID
     *
     * @param string $uuid
     * @return string
     */
    public function getGlobalReferenceByUuid($uuid)
    {
        return array_get($this->uuids['globals'], $uuid);
    }

    /**
     * Set all the global paths
     *
     * @param array $paths
     */
    public function setGlobalPaths($paths)
    {
        $this->paths['globals'] = $paths;
    }

    /**
     * Set a global path
     *
     * @param string $path The slug string
     * @param string $reference
     */
    public function setGlobalPath($path, $reference)
    {
        $this->paths['globals'][$path] = $reference;
    }

    /**
     * Remove a global path
     *
     * @param string $path The group/slug string
     */
    public function removeGlobalPath($path)
    {
        unset($this->paths['globals'][$path]);
    }

    /**
     * Get a global path by reference value
     *
     * @param string $reference
     * @return string
     */
    public function getGlobalPath($reference)
    {
        return array_get(array_flip($this->paths['globals']), $reference);
    }
}
