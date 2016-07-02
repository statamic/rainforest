<?php

namespace Statamic\Data\Content\File;

use Statamic\API\Path;
use Statamic\API\Config;
use Statamic\API\Helper;
use Statamic\API\Pattern;
use Statamic\Data\File\DataService;
use Statamic\API\Content as ContentAPI;
use Statamic\Contracts\Stache\ContentCacheService;
use Statamic\Contracts\Data\Content\ContentService as ContentServiceContract;

/**
 * A layer for interacting with and manipulating content
 */
class ContentService extends DataService implements ContentServiceContract
{
    /**
     * @var \Statamic\Stache\File\ContentCacheService
     */
    private $cache;

    /**
     * @var string
     */
    private $default_locale;

    /**
     * @param \Statamic\Contracts\Stache\ContentCacheService $cache
     */
    public function __construct(ContentCacheService $cache)
    {
        $this->cache = $cache;

        $this->default_locale = Config::getDefaultLocale();
    }

    /**
     * Get content by its UUID
     *
     * @param string       $uuid
     * @param string|null  $locale
     * @return \Statamic\Data\Content\ContentCollection
     */
    public function getUuid($uuid, $locale = null)
    {
        return $this->cache->getUuid($uuid, $locale);
    }

    /**
     * Get a single page
     *
     * @param string      $url
     * @param string|null $locale
     * @param bool        $fallback
     * @return \Statamic\Contracts\Data\Pages\Page
     */
    public function getPage($url, $locale = null, $fallback = false)
    {
        if ($page = $this->cache->getPage($url, $locale)) {
            return $page;
        }

        if ($fallback) {
            return $this->cache->getPage($url, $this->default_locale);
        }
    }

    /**
     * Get a single entry
     *
     * @param string      $slug
     * @param string      $collection
     * @param string|null $locale
     * @param bool        $fallback
     * @return \Statamic\Contracts\Data\Entries\Entry
     */
    public function getEntry($slug, $collection, $locale = null, $fallback = false)
    {
        if ($entry = $this->cache->getEntry($slug, $collection, $locale)) {
            return $entry;
        }

        if ($fallback) {
            return $this->cache->getEntry($slug, $collection, $this->default_locale);
        }
    }

    /**
     * Get multiple pages
     *
     * @param string|array|null $urls
     * @param string|null       $locale
     * @return \Statamic\Data\Pages\PageCollection
     */
    public function getPages($urls = null, $locale = null)
    {
        // No parameters passed, give them everything
        if (! $urls) {
            return $this->getAllPages($locale);
        }

        $urls = Helper::ensureArray($urls);
        $pages = collect_pages();

        foreach ($urls as $url) {
            $pages->put($url, $this->getPage($url));
        }

        return $pages;
    }

    /**
     * Get multiple entries
     *
     * @param string|array|null $slugs
     * @param string|array|null $collection
     * @param string|null       $locale
     * @param bool              $fallback
     * @return \Statamic\Data\Entries\EntryCollection
     */
    public function getEntries($slugs = null, $collection = null, $locale = null, $fallback = false)
    {
        // No parameters passed, give them everything
        if (! $slugs && ! $collection) {
            return $this->getAllEntries($locale, $fallback);
        }

        $entry_collection = collect_entries();

        if ($slugs && is_array($slugs)) {
            // If we have an been passed an array of slugs, we'll assume they've been passed
            // as "URLs" meaning they are formatted as ["collection/slug", ..., ...]
            foreach ($slugs as $key) {
                list($collection, $slug) = explode('/', $key);
                $entry_collection->put($key, $this->getEntry($slug, $collection, $locale));
            }

        } elseif (! $slugs && is_array($collection)) {
            // No slugs specified, and we've been given an array of collections.
            // We should get all the entries from the various collections.
            foreach ($collection as $collection_name) {
                foreach ($this->getEntries(null, $collection_name, $locale, $fallback) as $entry) {
                    $key = $collection_name . '/' . $entry->getSlug();
                    $entry_collection->put($key, $entry);
                }
            }

        } else {
            // No slugs specified, but there is a collection
            $entry_collection = $this->cache->getEntryCollection($collection, $locale);
            if ($fallback) {
                // If we want to get fallbacks, let's get them now and merge them in.
                $default_collection = $this->cache->getEntryCollection($collection, default_locale());
                $entry_collection = $default_collection->merge($entry_collection);
            }
        }

        return $entry_collection ?: collect_entries();
    }

    /**
     * Get all pages
     *
     * @param string|null $locale
     * @param bool        $fallback
     * @return \Statamic\Data\Pages\PageCollection
     */
    public function getAllPages($locale = null, $fallback = false)
    {
        $localized_pages = $this->cache->getPages($locale);

        // If we don't want to get the default fallbacks, we're done.
        if (! $fallback) {
            return collect_pages($localized_pages);
        }

        $default_pages = $this->cache->getPages($this->default_locale);

        return $default_pages->merge($localized_pages);
    }

    /**
     * Get all entries
     *
     * @param string|null $locale
     * @param bool        $fallback
     * @return \Statamic\Data\Entries\EntryCollection
     */
    public function getAllEntries($locale = null, $fallback = false)
    {
        $localized_entries = $this->flatten($this->cache->getEntryCollections($locale));

        // If we don't want to get the default fallbacks, we're done.
        if (! $fallback) {
            return collect_entries($localized_entries);
        }

        $default_entries = $this->flatten($this->cache->getEntryCollections($this->default_locale));

        $entries = $localized_entries + $default_entries;

        return collect_entries($entries);
    }

    /**
     * Rewrite the entries from a multidimensional to a flat array
     *
     * @param array $array
     * @return array
     */
    private function flatten($array)
    {
        $flattened = [];

        foreach ($array as $collection => $entries) {
            foreach ($entries as $uuid => $entry) {
                $flattened[$uuid] = $entry;
            }
        }

        return $flattened;
    }

    /**
     * Get all content (entries, pages, taxonomies, and globals combined)
     *
     * @param string|null $locale
     * @param bool        $fallback
     * @return \Statamic\Data\Content\ContentCollection
     */
    public function getAllContent($locale = null, $fallback = false)
    {
        $pages = $this->getAllPages($locale, $fallback);

        $entries = $this->getAllEntries($locale, $fallback);

        $taxonomies = $this->getAllTaxonomyTerms($locale, $fallback);

        $globals = $this->getAllGlobals($locale, $fallback);

        return collect_content($pages->merge($entries)->merge($taxonomies)->merge($globals));
    }

    /**
     * Get all the collections
     *
     * @param string|null $locale
     * @return \Statamic\Contracts\Data\Entries\CollectionFolder[]
     */
    public function getCollections($locale = null)
    {
        $collections = collect($this->cache->getCollections($locale));

        return $collections->sortBy(function ($collection) {
            return $collection->title();
        })->all();
    }

    /**
     * Get a collection
     *
     * @param string      $collection
     * @param string|null $locale
     * @return mixed
     */
    public function getCollection($collection, $locale = null)
    {
        return $this->cache->getCollection($collection, $locale);
    }

    /**
     * Gets a tree of content information
     *
     * @param string     $base_url
     * @param int        $depth
     * @param bool       $include_entries
     * @param bool       $show_drafts
     * @param array|bool $exclude
     * @return array
     */
    public function getContentTree(
        $base_url,
        $depth = null,
        $include_entries = false,
        $show_drafts = false,
        $exclude = false
    ) {
        $structure = $this->cache->getStructure();
        $depth = is_null($depth) ? INF : $depth;
        $output    = [];

        // Exclude URLs
        $exclude = Helper::ensureArray($exclude);

        // No depth asked for
        if ($depth == 0) {
            return [];
        }

        // Make sure we can find the requested URL in the structure
        if (! isset($structure[$base_url])) {
            return [];
        }

        // Depth measurements
        $starting_depth  = $structure[$base_url]['depth'] + 1;
        $current_depth   = $starting_depth;

        // Recursively grab the tree
        foreach ($structure as $url => $data) {
            // Is this the right depth and not the 404 page?
            if ($data['depth'] !== $current_depth || $url == "/404") {
                continue;
            }

            // Is this under the appropriate parent?
            if (! Pattern::startsWith(Path::tidy($data['parent'] . '/'), Path::tidy($base_url . '/'))) {
                continue;
            }

            // Draft?
            if (! $show_drafts && in_array($data['status'], ['draft', 'hidden'])) {
                continue;
            }

            // Is this in the excluded URLs list?
            if (in_array($url, $exclude)) {
                continue;
            }

            // Get information
            $content = $this->getPage($url, null, true);
            $content->setSupplement('depth', $current_depth);

            // Get entries belonging to this page. We'll treat them as child
            // pages and merge them into the children array later.
            $entries = [];
            if ($include_entries) {
                foreach (ContentAPI::pageRaw($url)->entries()->all() as $entry) {
                    $entries[] = [
                        'page' => $entry,
                        'depth' => $current_depth
                    ];
                }
            }

            // Get child pages
            $children = $this->getContentTree($url, $depth - 1, $include_entries, $show_drafts, $exclude);

            // Data to be returned to the tree
            $output[] = [
                'page' => $content,
                'depth' => $current_depth,
                'children' => array_merge($children, $entries)
            ];
        }

        // Sort by page order key
        uasort($output, function($one, $two) {
            return Helper::compareValues($one['page']->order(), $two['page']->order());
        });

        return array_values($output);
    }

    /**
     * Get a single taxonomy
     *
     * @param string      $slug
     * @param string      $taxonomy
     * @param string|null $locale
     * @param bool        $fallback
     * @return \Statamic\Contracts\Data\Taxonomies\TaxonomyTerm
     */
    public function getTaxonomyTerm($slug, $taxonomy, $locale = null, $fallback = false)
    {
        if ($term = $this->cache->getTaxonomyTerm($slug, $taxonomy, $locale)) {
            return $term;
        }

        if ($fallback) {
            return $this->cache->getTaxonomyTerm($slug, $taxonomy, $this->default_locale);
        }
    }

    /**
     * Get multiple taxonomy terms
     *
     * @param string|array|null $slugs
     * @param string|array|null $taxonomy
     * @param string|null       $locale
     * @param bool              $fallback
     * @return \Statamic\Data\Taxonomies\TermCollection
     */
    public function getTaxonomyTerms($slugs = null, $taxonomy = null, $locale = null, $fallback = false)
    {
        // No parameters passed, give them everything
        if (! $slugs && ! $taxonomy) {
            return $this->getAllTaxonomyTerms($locale, $fallback);
        }

        $collection = collect_terms();

        if ($slugs && is_array($slugs)) {
            // If we have an been passed an array of slugs, we'll assume they've been passed
            // as "URLs" meaning they are formatted as ["group/slug", ..., ...]
            foreach ($slugs as $key) {
                list($taxonomy, $slug) = explode('/', $key);
                $collection->put($key, $this->getTaxonomyTerm($slug, $taxonomy, $locale));
            }

        } elseif (! $slugs && is_array($taxonomy)) {
            // No slugs specified, and we've been given an array of taxonomies.
            // We should get all the taxonomies from the various taxonomies.
            foreach ($taxonomy as $taxonomy_name) {
                foreach ($this->getTaxonomyTerms(null, $taxonomy_name, $locale) as $entry) {
                    $key = $taxonomy_name . '/' . $entry->getSlug();
                    $collection->put($key, $entry);
                }
            }

        } else {
            // No slugs specified, but there is a collection
            $terms = $this->cache->getTaxonomyTerms($locale);
            $terms = array_get($terms, $taxonomy, []);
            if ($fallback) {
                // If we want to get fallbacks, let's get them now and merge them in.
                $default_terms = $this->cache->getTaxonomyTerms($this->default_locale);
                $default_terms = array_get($default_terms, $taxonomy);
                $terms = $terms + $default_terms;
            }
            $collection = collect_terms($terms);
        }

        return $collection;
    }

    /**
     * Get all taxonomy terms
     *
     * @param string|null $locale
     * @param bool        $fallback
     * @return \Statamic\Data\Taxonomies\TermCollection
     */
    public function getAllTaxonomyTerms($locale = null, $fallback = false)
    {
        $localized_taxonomies = $this->flatten($this->cache->getTaxonomyTerms($locale));

        // If we don't want to get the default fallbacks, we're done.
        if (! $fallback) {
            return collect_terms($localized_taxonomies);
        }

        $default_taxonomies = $this->flatten($this->cache->getTaxonomyTerms(Config::getDefaultLocale()));

        $taxonomies = $localized_taxonomies + $default_taxonomies;

        return collect_terms($taxonomies);
    }

    /**
     * Get all the taxonomies
     *
     * @return \Statamic\Contracts\Data\Taxonomies\Taxonomy[]
     */
    public function getTaxonomies()
    {
        $taxonomies = collect($this->cache->getTaxonomies());

        return $taxonomies->sortBy(function ($taxonomy) {
            return $taxonomy->title();
        })->all();
    }

    /**
     * Get a taxonomy
     *
     * @param string      $taxonomy
     * @param string|null $locale
     * @return mixed
     */
    public function getTaxonomy($taxonomy, $locale = null)
    {
        return $this->cache->getTaxonomy($taxonomy, $locale);
    }

    /**
     * @param string      $slug
     * @param string|null $locale
     * @param bool        $fallback
     * @return \Statamic\Contracts\Data\Globals\GlobalContent
     */
    public function getGlobal($slug, $locale = null, $fallback = false)
    {
        if ($page = $this->cache->getGlobal($slug, $locale)) {
            return $page;
        }

        if ($fallback) {
            return $this->cache->getGlobal($slug, $this->default_locale);
        }
    }

    /**
     * Get multiple globals
     *
     * @param string|null $slugs
     * @param string|null $locale
     * @param bool        $fallback
     * @return \Statamic\Contracts\Data\Globals\GlobalContent
     */
    public function getGlobals($slugs = null, $locale = null, $fallback = false)
    {
        // No parameters passed, give them everything
        if (! $slugs) {
            return $this->getAllGlobals($locale, $fallback);
        }

        $slugs = Helper::ensureArray($slugs);
        $globals = collect_globals();

        foreach ($slugs as $slug) {
            $globals->put($slug, $this->getGlobal($slug, $locale, $fallback));
        }

        return $globals;
    }

    /**
     * Get all globals
     *
     * @param string|null $locale
     * @param bool        $fallback
     * @return \Statamic\Data\Globals\GlobalCollection
     */
    public function getAllGlobals($locale = null, $fallback = false)
    {
        $localized_globals = $this->cache->getGlobals($locale);

        // If we don't want to get the default fallbacks, we're done.
        if (! $fallback) {
            return collect_globals($localized_globals);
        }

        $default_globals = $this->cache->getGlobals($this->default_locale);

        return $default_globals->merge($localized_globals);
    }

    /**
     * @param string|null $locale
     * @return \Statamic\Contracts\Data\Pages\PageFolder[]
     */
    public function getPageFolders($locale = null)
    {
        return $this->cache->getPageFolders($locale);
    }

    /**
     * Get a single page folder
     *
     * @param string      $path
     * @param string|null $locale
     * @return \Statamic\Contracts\Data\Pages\PageFolder
     */
    public function getPageFolder($path, $locale = null)
    {
        return $this->cache->getPageFolder($path, $locale);
    }
}
