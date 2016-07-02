<?php

namespace Statamic\Contracts\Data\Content;

use Statamic\Contracts\Data\DataService;

interface ContentService extends DataService
{
    /**
     * Get a single page
     *
     * @param string      $url
     * @param string|null $locale
     * @return \Statamic\Data\Page
     */
    public function getPage($url, $locale = null);

    /**
     * Get a single entry
     *
     * @param string      $slug
     * @param string      $collection
     * @param string|null $locale
     * @return \Statamic\Data\Entry
     */
    public function getEntry($slug, $collection, $locale = null);

    /**
     * Get a single taxonomy term
     *
     * @param string      $slug
     * @param string      $taxonomy
     * @param string|null $locale
     * @return \Statamic\Contracts\Data\Taxonomies\Term
     */
    public function getTaxonomyTerm($slug, $taxonomy, $locale = null);

    /**
     * @param string      $slug
     * @param string|null $locale
     * @return \Statamic\Data\GlobalContent
     */
    public function getGlobal($slug, $locale = null);

    /**
     * Get multiple pages
     *
     * @param string|array|null $urls
     * @param string|null       $locale
     * @return \Statamic\Data\PageCollection
     */
    public function getPages($urls = null, $locale = null);

    /**
     * Get multiple entries
     *
     * @param string|array|null   $slugs
     * @param string|array|null   $collection
     * @param string|null         $locale
     * @return \Statamic\Data\EntryCollection
     */
    public function getEntries($slugs = null, $collection = null, $locale = null);

    /**
     * Get multiple taxonomy terms
     *
     * @param string|array|null   $slugs
     * @param string|array|null   $taxonomy
     * @param string|null         $locale
     * @return \Statamic\Data\Taxonomies\TermCollection
     */
    public function getTaxonomyTerms($slugs = null, $taxonomy = null, $locale = null);

    /**
     * Get multiple globals
     *
     * @param string|null $slugs
     * @param string|null $locale
     * @return \Statamic\Data\GlobalContent
     */
    public function getGlobals($slugs = null, $locale = null);

    /**
     * Get all pages
     *
     * @param string|null $locale
     * @return \Statamic\Data\PageCollection
     */
    public function getAllPages($locale = null);

    /**
     * Get all entries
     *
     * @param string|null $locale
     * @return \Statamic\Data\EntryCollection
     */
    public function getAllEntries($locale = null);

    /**
     * Get all globals
     *
     * @param string|null $locale
     * @return \Statamic\Data\GlobalCollection
     */
    public function getAllGlobals($locale = null);

    /**
     * Get all content (entries and pages combined)
     *
     * @param string|null $locale
     * @return \Statamic\Data\ContentCollection
     */
    public function getAllContent($locale = null);

    /**
     * Get all taxonomy terms
     *
     * @param string|null $locale
     * @return \Statamic\Data\Taxonomies\TermCollection
     */
    public function getAllTaxonomyTerms($locale = null);

    /**
     * Get all the collections
     *
     * @param string|null $locale
     * @return \Statamic\Data\EntryCollection[]
     */
    public function getCollections($locale = null);

    /**
     * Get a collection
     *
     * @param string      $collection
     * @param string|null $locale
     * @return mixed
     */
    public function getCollection($collection, $locale = null);

    /**
     * Get all the taxonomies
     *
     * @return \Statamic\Contracts\Data\Taxonomies\Taxonomy[]
     */
    public function getTaxonomies();

    /**
     * Get a taxonomy
     *
     * @param string      $taxonomy
     * @param string|null $locale
     * @return \Statamic\Contracts\Data\Taxonomies\Taxonomy
     */
    public function getTaxonomy($taxonomy, $locale = null);

    /**
     * Get all page folders
     *
     * @param string|null $locale
     * @return \Statamic\Contracts\Data\Pages\PageFolder[]
     */
    public function getPageFolders($locale = null);

    /**
     * Get a single page folder
     *
     * @param string      $path
     * @param string|null $locale
     * @return \Statamic\Contracts\Data\Pages\PageFolder
     */
    public function getPageFolder($path, $locale = null);

    /**
     * Get content in a tree based format
     *
     * @param string $base_url
     * @param int    $depth
     * @param bool   $include_entries
     * @param bool   $show_drafts
     * @param bool   $exclude
     * @return array
     */
    public function getContentTree(
        $base_url,
        $depth = null,
        $include_entries = false,
        $show_drafts = false,
        $exclude = false
    );
}
