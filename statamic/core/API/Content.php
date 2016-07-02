<?php

namespace Statamic\API;

use Statamic\Data\ContentCollection;

/**
 * Interacting with the content
 */
class Content
{
    /**
     * @return \Statamic\Contracts\Data\Content\ContentService
     */
    private static function content()
    {
        return app('Statamic\Contracts\Data\Content\ContentService');
    }

    /**
     * Get the raw content object by UUID
     *
     * @param string      $uuid
     * @param string|null $locale
     * @return \Statamic\Data\Content
     */
    public static function uuidRaw($uuid, $locale = null)
    {
        return self::content()->getUuid($uuid, $locale);
    }

    /**
     * Get the content from a UUID
     *
     * @param string      $uuid
     * @param string|null $locale
     * @return array
     */
    public static function uuid($uuid, $locale = null)
    {
        return self::uuidRaw($uuid, $locale)->toArray();
    }

    /**
     * Get the raw Page object for a single URL
     *
     * @param string      $url    URL to find
     * @param string|null $locale Optional locale to use
     * @param bool        $fallback Whether to fallback to the default locale
     * @return \Statamic\Data\Page
     */
    public static function pageRaw($url, $locale = null, $fallback = false)
    {
        return self::content()->getPage($url, $locale, $fallback);
    }

    /**
     * Get content for a single URL
     *
     * @param string       $url     URL to find
     * @param string|null  $locale  Optional locale to use
     * @return array
     */
    public static function page($url, $locale = null)
    {
        return self::pageRaw($url, $locale)->toArray();
    }

    /**
     * Get the raw Entry object for a slug
     *
     * @param string      $slug       Slug to find
     * @param string      $collection Collection to look inside
     * @param string|null $locale     Optional locale to use
     * @param bool        $fallback   Whether to fallback to the default locale
     * @return \Statamic\Data\Entry
     */
    public static function entryRaw($slug, $collection, $locale = null, $fallback = false)
    {
        return self::content()->getEntry($slug, $collection, $locale, $fallback);
    }

    /**
     * Get the content for an entry
     *
     * @param string      $collection
     * @param string      $slug
     * @param string|null $locale
     * @return mixed
     */
    public static function entry($collection, $slug, $locale = null)
    {
        return self::entryRaw($collection, $slug, $locale)->toArray();
    }

    /**
     * Get the raw Taxonomy object for a slug
     *
     * @param string      $slug   Slug to find
     * @param string      $taxonomy  Taxonomy to look inside
     * @param string|null $locale Optional locale to use
     * @param bool        $fallback Whether the get the fallbacks from the default locale
     * @return \Statamic\Contracts\Data\Taxonomies\Term
     */
    public static function taxonomyTermRaw($slug, $taxonomy, $locale = null, $fallback = false)
    {
        return self::content()->getTaxonomyTerm($slug, $taxonomy, $locale, $fallback);
    }

    /**
     * Get the content for a taxonomy
     *
     * @param string      $taxonomy
     * @param string      $slug
     * @param string|null $locale
     * @return mixed
     */
    public static function taxonomyTerm($taxonomy, $slug, $locale = null)
    {
        return self::taxonomyTermRaw($taxonomy, $slug, $locale)->toArray();
    }

    /**
     * Get the raw Content object for a URL
     *
     * @param string      $url       The URL to look for
     * @param string|null $locale    The locale to search
     * @param bool        $fallback  Whether to fall back to the default locale
     * @return \Statamic\Data\Entry|\Statamic\Data\Page
     */
    public static function getRaw($url, $locale = null, $fallback = false)
    {
        $is_array   = is_array($url);
        $urls       = Helper::ensureArray($url);
        $collection = collect_content();

        foreach ($urls as $url) {

            $url = Str::ensureLeft($url, '/');

            if ($page = self::pageRaw($url, $locale, $fallback)) {

                // Attempt to get a page
                $collection = $collection->push($page);

            } elseif ($entry = self::entryByUrlRaw($url, $locale, $fallback)) {
                // Attempt to find an entry URL from mounting points
                $collection = $collection->push($entry);

            } elseif ($taxonomy = self::taxonomyTermByUrlRaw($url, $locale, $fallback)) {
                // Attempt to find a taxonomy route
                $collection = $collection->push($taxonomy);
            }
        }

        return ($is_array) ? $collection : $collection->first();
    }

    /**
     * Get the content from a URL
     *
     * @param string|array $url
     * @param string|null  $locale
     * @return \Statamic\Data\Content|\Statamic\Data\ContentCollection
     */
    public static function get($url, $locale = null)
    {
        $is_array = is_array($url);
        $urls     = Helper::ensureArray($url);

        $collection = self::getRaw($urls);

        return ($is_array) ? $collection->toArray() : $collection->first()->toArray();
    }

    /**
     * Get the raw Entry object by URL
     *
     * @param string      $url       The URL to look for
     * @param string|null $locale    The locale to search
     * @param bool        $fallback  Whether to fall back to the default locale
     * @return \Statamic\Data\Entry
     */
    public static function entryByUrlRaw($url, $locale = null, $fallback = false)
    {
        return self::entries(null, $locale, $fallback)->filter(function($entry) use ($url) {
            return $entry->urlPath() === $url;
        })->first();
    }

    /**
     * Get an entry's content by URL
     *
     * @param string      $url
     * @param string|null $locale
     * @return \Statamic\Data\Entry
     */
    public static function entryByUrl($url, $locale = null)
    {
        return self::entryByUrlRaw($url, $locale)->toArray();
    }

    /**
     * Get the raw Taxonomy term object by URL
     *
     * @param string      $url
     * @param string|null $locale
     * @param bool        $fallback
     * @return \Statamic\Contracts\Data\Taxonomies\Term
     */
    public static function taxonomyTermByUrlRaw($url, $locale = null, $fallback = false)
    {
        $term = Content::taxonomyTerms(null, $locale, $fallback)->filter(function($term) use ($url) {
            return $term->urlPath() == $url;
        })->first();

        return $term ?: null;
    }

    /**
     * Get a taxonomy term's content by URL
     *
     * @param string      $url
     * @param string|null $locale
     * @return \Statamic\Contracts\Data\Taxonomies\Term
     */
    public static function taxonomyTermByUrl($url, $locale = null)
    {
        return self::taxonomyTermByUrlRaw($url, $locale)->toArray();
    }

    /**
     * Get all content
     *
     * @return \Statamic\Data\ContentCollection
     */
    public static function all()
    {
        return self::content()->getAllContent();
    }

    /**
     * Get all entries
     *
     * @param string|null $collection Collection to get entries from
     * @param string|null $locale
     * @param bool        $fallback
     * @return \Statamic\Data\Entries\EntryCollection
     */
    public static function entries($collection = null, $locale = null, $fallback = false)
    {
        if ($collection) {
            return self::content()->getEntries(null, $collection, $locale, $fallback);
        }

        return self::content()->getAllEntries($locale, $fallback);
    }

    /**
     * Get all pages
     *
     * @param array|null  $urls
     * @param string|null $locale
     * @param bool        $fallback
     * @return \Statamic\Data\ContentCollection
     */
    public static function pages($urls = null, $locale = null, $fallback = false)
    {
        if ($urls) {
            return self::content()->getPages($urls, $locale, $fallback);
        }

        return self::content()->getAllPages($locale, $fallback);
    }

    /**
     * Get all taxonomies
     *
     * @param string|null $group Group to get entries from
     * @param string|null $locale
     * @param bool        $fallback
     * @return \Statamic\Data\Taxonomies\TermCollection
     */
    public static function taxonomyTerms($group = null, $locale = null, $fallback = false)
    {
        if ($group) {
            return self::content()->getTaxonomyTerms(null, $group, $locale, $fallback);
        }

        return self::content()->getAllTaxonomyTerms($locale, $fallback);
    }

    /**
     * Get all globals
     *
     * @param array|null  $slugs
     * @param string|null $locale
     * @param bool        $fallback
     * @return \Statamic\Data\Globals\GlobalCollection
     */
    public static function globals($slugs = null, $locale = null, $fallback = false)
    {
        if ($slugs) {
            return self::content()->getGlobals($slugs, $locale, $fallback);
        }

        return self::content()->getAllGlobals($locale, $fallback);
    }

    /**
     * Get a global set
     *
     * @param      $slug
     * @param null $locale
     * @return \Statamic\Contracts\Data\Globals\GlobalContent
     */
    public static function globalSet($slug, $locale = null)
    {
        return self::globals($slug, $locale)->first();
    }

    /**
     * Get all collections
     *
     * @return \Statamic\Contracts\Data\Entries\CollectionFolder[]
     */
    public static function collections($locale = null)
    {
        return self::content()->getCollections($locale);
    }

    /**
     * @param string      $collection
     * @param string|null $locale
     * @return \Statamic\Contracts\Data\Entries\CollectionFolder
     */
    public static function collection($collection, $locale = null)
    {
        return self::content()->getCollection($collection, $locale);
    }

    /**
     * Get the names of all the collections
     *
     * @return array
     */
    public static function collectionNames()
    {
        return array_keys(self::collections());
    }

    /**
     * Check if a collection exists
     *
     * @param string $collection
     * @return bool
     */
    public static function collectionExists($collection)
    {
        return in_array($collection, array_keys(self::collections()));
    }

    /**
     * Check if a entry exists
     *
     * @param string      $slug
     * @param string      $collection
     * @param string|null $locale
     * @return bool
     */
    public static function entryExists($slug, $collection, $locale = null)
    {
        return (bool) self::entryRaw($slug, $collection, $locale);
    }

    /**
     * Check if a page exists
     *
     * @param string      $url
     * @param string|null $locale
     * @return bool
     */
    public static function pageExists($url, $locale)
    {
        return (bool) self::pageRaw($url, $locale);
    }

    /**
     * Check if a taxonomy exists
     *
     * @param string      $slug
     * @param string      $group
     * @param string|null $locale
     * @return bool
     */
    public static function taxonomyTermExists($slug, $group, $locale = null)
    {
        return (bool) self::taxonomyTermRaw($slug, $group, $locale);
    }

    /**
     * Check if content exists at a URL
     *
     * @param string      $url
     * @param string|null $locale
     * @return bool
     */
    public static function exists($url, $locale = null)
    {
        return (bool) self::getRaw($url, $locale);
    }

    /**
     * Get all taxonomies
     *
     * @return \Statamic\Contracts\Data\Taxonomies\Taxonomy[]
     */
    public static function taxonomies()
    {
        return self::content()->getTaxonomies();
    }

    /**
     * Get a taxonomy
     *
     * @param string      $taxonomy
     * @param string|null $locale
     * @return \Statamic\Contracts\Data\Taxonomies\Taxonomy
     */
    public static function taxonomy($taxonomy, $locale = null)
    {
        return self::content()->getTaxonomy($taxonomy, $locale);
    }

    /**
     * Check if a taxonomy exists
     *
     * @param string $taxonomy
     * @return bool
     */
    public static function taxonomyExists($taxonomy)
    {
        return in_array($taxonomy, array_keys(self::taxonomies()));
    }

    /**
     * @return array
     */
    public static function taxonomyNames()
    {
        return array_keys(self::taxonomies());
    }

    /**
     * @return \Statamic\Contracts\Data\Pages\PageFolder[]
     */
    public static function pageFolders()
    {
        return self::content()->getPageFolders();
    }

    /**
     * @param string $path
     * @return \Statamic\Contracts\Data\Pages\PageFolder
     */
    public static function pageFolder($path)
    {
        return self::content()->getPageFolder($path);
    }

    /**
     * Get the content tree
     *
     * @param string $url
     * @param int    $depth
     * @param bool   $entries
     * @param bool   $hidden
     * @param bool   $drafts
     * @param bool   $exclude
     * @return array
     */
    public static function tree(
        $url = null,
        $depth = null,
        $entries = null,
        $drafts = null,
        $exclude = null
    ) {
        return self::content()->getContentTree($url, $depth, $entries, $drafts, $exclude);
    }
}
