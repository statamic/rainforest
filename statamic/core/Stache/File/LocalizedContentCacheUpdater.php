<?php

namespace Statamic\Stache\File;

use Statamic\API\URL;
use Statamic\API\File;
use Statamic\API\Path;
use Statamic\API\YAML;
use Statamic\API\Page;
use Statamic\API\Entry;
use Statamic\API\Config;
use Statamic\API\Helper;
use Statamic\API\Str;
use Statamic\API\Globals;
use Statamic\API\Pattern;
use Statamic\API\TaxonomyTerm;
use Illuminate\Support\Collection;
use Statamic\Data\File\OrderParser;
use Statamic\Data\File\StatusParser;
use Statamic\Exceptions\FatalException;
use Statamic\Contracts\Stache\ContentCache as ContentCacheContract;
use Statamic\Contracts\Stache\LocalizedContentCache as LocalizedContentCacheContract;
use Statamic\Contracts\Stache\LocalizedContentCacheUpdater as LocalizedContentCacheUpdaterContract;

class LocalizedContentCacheUpdater implements LocalizedContentCacheUpdaterContract
{
    /**
     * @var \Statamic\Stache\File\ContentCache
     */
    private $cache;

    /**
     * @var \Statamic\Stache\File\LocalizedContentCache
     */
    private $localized_cache;

    /**
     * @var array
     */
    private $locales;

    /**
     * @var string
     */
    private $active_locale;

    /**
     * @var string
     */
    private $default_locale;

    /**
     * @var array
     */
    private $other_locales;

    /**
     * @var \Statamic\Data\File\OrderParser
     */
    private $order_parser;

    /**
     * @var \Statamic\Data\File\StatusParser
     */
    private $status_parser;

    /**
     * @param \Statamic\Contracts\Stache\ContentCache $cache
     */
    public function __construct(ContentCacheContract $cache)
    {
        $this->cache = $cache;

        $this->order_parser = app('Statamic\Contracts\Data\Content\OrderParser');
        $this->status_parser = app('Statamic\Contracts\Data\Content\StatusParser');
    }

    /**
     * @param \Statamic\Contracts\Stache\LocalizedContentCache $localized_cache
     * @return \Statamic\Contracts\Stache\LocalizedContentCache
     */
    public function update(LocalizedContentCacheContract $localized_cache)
    {
        $this->localized_cache = $localized_cache;

        $this->setUpLocaleData();

        $this->removeDeletedPages();
        $this->removeDeletedPageFolders();
        $this->removeDeletedEntries();
        $this->removeDeletedCollections();
        $this->removeDeletedTaxonomyTerms();
        $this->removeDeletedTaxonomies();
        $this->removeDeletedGlobals();

        $this->updateTaxonomyTerms();
        $this->updateTaxonomies();
        $this->updateEntries();
        $this->updateCollections();
        $this->updatePages();
        $this->updatePageFolders();
        $this->updateGlobals();

        return $this->localized_cache;
    }

    /**
     * Set up the locale data
     */
    private function setUpLocaleData()
    {
        $this->locales = Config::getLocales();
        $this->active_locale  = $this->localized_cache->getLocale();
        $this->default_locale = reset($this->locales);
        $this->other_locales  = array_diff($this->locales, [$this->active_locale]);
    }

    /**
     * Update entries
     */
    private function updateEntries()
    {
        $files = $this->getLocalizedFiles($this->cache->getModifiedEntryFiles());

        foreach ($files as $path) {
            $folder = $this->getFolderFromPath($path);

            $this->cache->setTimestamp($path, File::disk('content')->lastModified($path));

            $clean_path = Path::clean($path); // Remove flags and order keys
            $slug = pathinfo($clean_path)['filename'];

            $entry = $this->createEntry($slug, $path);
            $uuid = $entry->id();

            $string = $folder . '/' . $slug;

            $this->localized_cache->setEntry($uuid, $folder, $entry);
            $this->localized_cache->setEntryUuid($uuid, $string);
            $this->localized_cache->setEntryPath($string, $path);

            if ($this->active_locale !== $this->default_locale) {
                // If the entry has a localized slug, we'll need to save it.
                $localized_slug = $entry->getSlug();
                if ($localized_slug !== $slug) {
                    $this->localized_cache->setLocalizedEntrySlug($uuid, $localized_slug);
                }
            }
        }
    }

    private function updateCollections()
    {
        $files = $this->cache->getModifiedCollectionFiles();

        // Get a list of collection names, and add the timestamp to the cache
        $collections = [];
        foreach ($files as $path) {
            $folder = $this->getFolderFromPath($path);
            $collections[] = $folder;
            $this->cache->setTimestamp($path, File::disk('content')->lastModified($path));
        }
        $collections = array_unique($collections);

        // Update each collection
        foreach ($collections as $collection_name) {
            $yaml = File::disk('content')->get("collections/$collection_name/folder.yaml");
            $data = YAML::parse($yaml);
            $collection_folder = app('Statamic\Contracts\Data\Entries\CollectionFolder');
            $collection_folder->path($collection_name);
            $collection_folder->data($data);
            $this->localized_cache->setCollection($collection_name, $collection_folder);
        }
    }

    /**
     * Get the entry/taxonomy folder from a path
     *
     * @param string $path
     * @return string
     */
    private function getFolderFromPath($path)
    {
        $path = preg_replace('#^(?:collections|taxonomies)/#', '', $path);

        return explode('/', $path)[0];
    }

    /**
     * Update pages
     */
    private function updatePages()
    {
        $files = $this->getLocalizedFiles(
            collect_files($this->cache->getModifiedPageFiles())->rejectByExtension('yaml')->all()
        );

        foreach ($files as $path) {
            $this->cache->setTimestamp($path, File::disk('content')->lastModified($path));

            $url = URL::buildFromPath($path);
            $slug = URL::slug($url);

            $page = $this->createPage($url, $path);
            $uuid = $page->id();

            $this->localized_cache->setPage($uuid, $page);
            $this->localized_cache->setPageUuid($uuid, $url);
            $this->localized_cache->setPageUrl($url, $path);

            if ($this->active_locale === $this->default_locale) {
                $this->localized_cache->setStructure($url, $this->createPageStructure($url, $path));
            }

            if ($this->active_locale !== $this->default_locale) {
                // If the page has a localized slug, we'll need to save it.
                $localized_slug = $page->slug();
                if ($localized_slug !== $slug) {
                    $this->localized_cache->setLocalizedPageSlug($uuid, $localized_slug);
                }
            }
        }


        if ($this->active_locale !== $this->default_locale) {
            $this->updateLocalizedUrls();
        }
    }

    private function updatePageFolders()
    {
        $files = $this->getLocalizedFiles(
            collect_files($this->cache->getModifiedPageFiles())->filterByExtension('yaml')->all()
        );

        foreach ($files as $path) {
            $this->cache->setTimestamp($path, File::disk('content')->lastModified($path));

            $data = YAML::parse(File::disk('content')->get($path));

            $page_folder = app('Statamic\Contracts\Data\Pages\PageFolder');
            $page_folder->path($path);
            $page_folder->data($data);

            $this->localized_cache->setPageFolder(
                Path::directory(Path::clean(preg_replace('/site\/content\/pages\//', '', $path))),
                $page_folder
            );
        }
    }

    /**
     * Update localized URLs
     *
     * URLs that have don't have a localized slug, but have ancestor
     * pages that do, will need to have their own URLs updated.
     */
    private function updateLocalizedUrls()
    {
        // If there are no localized slugs, there are no URLs to localize.
        $localized_slugs = array_get($this->localized_cache->getLocalizedSlugs(), 'pages');
        if (empty($localized_slugs)) {
            return;
        }

        // Get all the default urls. We'll be taking these, modifying them and using
        // the resulting array in the cache for our localized urls for this locale.
        $urls = $this->cache->getLocale(Config::getDefaultLocale())->getUuids()['pages'];

        // Don't need the homepage
        unset($urls[array_search('/', $urls)]);

        // Sort by shallowest first, so we can localize parents first
        uasort($urls, function($a, $b) {
            return (substr_count($a, '/') >= substr_count($b, '/')) ? 1 : -1;
        });

        foreach ($localized_slugs as $uuid => $localized_slug) {
            // First update the corresponding URL
            $localized_slug_corresponding_url = $urls[$uuid];
            $updated_url = URL::replaceSlug($localized_slug_corresponding_url, $localized_slug);
            $urls[$uuid] = $updated_url;

            // Then replace the start of all child urls with the newly updated url
            foreach ($urls as $key => $default_url) {
                if (Str::startsWith($default_url, $localized_slug_corresponding_url.'/')) {
                    $one = explode('/', $default_url);
                    $two = explode('/', $updated_url);
                    $urls[$key] = join('/', array_replace($one, $two));
                }
            }
        }

        $this->localized_cache->setLocalizedUrls($urls);
    }

    /**
     * Update taxonomy terms
     */
    private function updateTaxonomyTerms()
    {
        $files = $this->getLocalizedFiles($this->cache->getModifiedTaxonomyFiles());

        foreach ($files as $path) {
            $folder = $this->getFolderFromPath($path);

            $this->cache->setTimestamp($path, File::disk('content')->lastModified($path));

            $clean_path = Path::clean($path); // Remove flags and order keys
            $slug = pathinfo($clean_path)['filename'];

            $term = $this->createTaxonomyTerm($slug, $path);
            $uuid = $term->id();

            $string = $folder . '/' . $slug;

            $this->localized_cache->setTaxonomyTerm($uuid, $folder, $term);
            $this->localized_cache->setTaxonomyUuid($uuid, $string);
            $this->localized_cache->setTaxonomyPath($string, $path);

            if ($this->active_locale !== $this->default_locale) {
                // If the taxonomy has a localized slug, we'll need to save it.
                $localized_slug = $term->getSlug();
                if ($localized_slug !== $slug) {
                    $this->localized_cache->setLocalizedTaxonomySlug($uuid, $localized_slug);
                }
            }
        }
    }

    private function updateTaxonomies()
    {
        $files = $this->cache->getModifiedTaxonomyGroupFiles();

        // Get a list of group names, add timestamp to the cache
        $groups = [];
        foreach ($files as $path) {
            $folder = $this->getFolderFromPath($path);
            $groups[] = $folder;
            $this->cache->setTimestamp($path, File::disk('content')->lastModified($path));
        }
        $groups = array_unique($groups);

        // Update each group
        foreach ($groups as $group_name) {
            $data = YAML::parse(File::disk('content')->get("taxonomies/$group_name/folder.yaml"));
            $taxonomy_folder = app('Statamic\Contracts\Data\Taxonomies\Taxonomy');
            $taxonomy_folder->path($group_name);
            $taxonomy_folder->data($data);
            $this->localized_cache->setTaxonomy($group_name, $taxonomy_folder);
        }
    }

    /**
     * Update globals
     */
    private function updateGlobals()
    {
        $files = $this->getLocalizedFiles($this->cache->getModifiedGlobalsFiles());

        foreach ($files as $path) {
            $this->cache->setTimestamp($path, File::disk('content')->lastModified($path));

            $slug = pathinfo($path)['filename'];

            $global = $this->createGlobal($slug, $path);
            $uuid = $global->id();

            $this->localized_cache->setGlobal($uuid, $global);
            $this->localized_cache->setGlobalUuid($uuid, $slug);
            $this->localized_cache->setGlobalPath($slug, $path);
        }
    }

    /**
     * Create a page object
     *
     * @param string $url
     * @param string $path
     * @return \Statamic\Contracts\Data\Pages\Page
     */
    private function createPage($url, $path)
    {
        list($front_matter, $uuid) = $this->buildFrontMatter($path);

        $page = Page::create($url)
                ->locale($this->localized_cache->getLocale())
                ->with($front_matter)
                ->published($this->status_parser->pagePublished($path))
                ->order($this->order_parser->getPageOrder($path))
                ->path($path)
                ->get();

        if (! $uuid) {
            $page->path($path);
            $page->ensureId(true);
        }

        return $page;
    }

    /**
     * Create an entry object
     *
     * @param string $slug
     * @param string $path
     * @return \Statamic\Contracts\Data\Entries\Entry
     */
    private function createEntry($slug, $path)
    {
        $collection = explode('/', $path)[1];

        list($front_matter, $uuid) = $this->buildFrontMatter($path);

        $entry = Entry::create($slug)
                 ->collection($collection)
                 ->locale($this->localized_cache->getLocale())
                 ->with($front_matter)
                 ->published($this->status_parser->entryPublished($path))
                 ->order($this->order_parser->getEntryOrder($path))
                 ->path($path)
                 ->get();

        if (! $uuid) {
            $entry->ensureId(true);
        }

        return $entry;
    }

    /**
     * Create a taxonomy object
     *
     * @param string $slug
     * @param string $path
     * @return \Statamic\Contracts\Data\Taxonomies\Term
     */
    private function createTaxonomyTerm($slug, $path)
    {
        $taxonomy = explode('/', $path)[1];

        list($front_matter, $uuid) = $this->buildFrontMatter($path);

        $taxonomy = TaxonomyTerm::create($slug)
                    ->taxonomy($taxonomy)
                    ->locale($this->localized_cache->getLocale())
                    ->with($front_matter)
                    ->published($this->status_parser->entryPublished($path))
                    ->order($this->order_parser->getEntryOrder($path))
                    ->path($path)
                    ->get();

        if (! $uuid) {
            $taxonomy->ensureId(true);
        }

        return $taxonomy;
    }

    /**
     * Create a global object
     *
     * @param string $slug
     * @param string $path
     * @return \Statamic\Contracts\Data\Globals\GlobalContent
     */
    private function createGlobal($slug, $path)
    {
        list($front_matter, $uuid) = $this->buildFrontMatter($path);

        $global = Globals::create($slug)
                         ->locale($this->localized_cache->getLocale())
                         ->with($front_matter)
                         ->get();

        if (! $uuid) {
            $global->path($path);
            $global->ensureId(true);
        }

        return $global;
    }

    /**
     * Build the front matter to be used in a content object
     *
     * @param string $path
     * @return array
     * @throws \Statamic\Exceptions\FatalException
     */
    private function buildFrontMatter($path)
    {
        $front_matter = YAML::parse(File::disk('content')->get($path));

        $uuid = array_get($front_matter, 'id');

        // If this is a localized page, merge in the front matter from the default
        if ($this->active_locale !== $this->default_locale) {
            // If there's no UUID, we can't proceed so we'll throw an exception.
            if (! $uuid) {
                throw new FatalException("Cache cannot be built correctly because the file is missing a UUID. [$path]");
            }

            if ($default = $this->cache->getLocale($this->default_locale)->getContent($uuid)->path()) {
                $front_matter = array_merge(
                    YAML::parse(File::disk('content')->get($default)),
                    $front_matter
                );
            }
        }

        // Allow auto-discovery/creation of text based taxonomy terms.
        // If any are discovered or created, the values will be replaced.
        $front_matter = $this->autoTaxonomize($path, $front_matter);

        return [$front_matter, $uuid];
    }

    private function autoTaxonomize($path, $data)
    {
        // If there are no taxonomy fields defined, the feature is disabled.
        if (! $taxonomy_fields = Config::get('system.auto_taxonomy_fields')) {
            return $data;
        }

        $updated = false;

        foreach ($taxonomy_fields as $field => $taxonomy) {
            // Field doesn't exist or has no value. Move along.
            if (! $field_data = array_get($data, $field)) {
                continue;
            }

            $is_string = is_string($field_data);

            $field_data = Helper::ensureArray($field_data);

            foreach ($field_data as $term_key => $term_value) {
                if (Pattern::isUUID($term_value)) {
                    continue;
                }

                $slug = Str::slug($term_value);

                // Attempt to get an existing taxonomy by name
                if (! $term_id = $this->localized_cache->getTaxonomyUuid("{$taxonomy}/{$slug}")) {
                    // It doesn't exist. We'll create it and add it to the cache.
                    $term_id = Helper::makeUuid();
                    $taxonomy_term = TaxonomyTerm::create($slug)
                                     ->taxonomy($taxonomy)
                                     ->locale($this->localized_cache->getLocale())
                                     ->with(['id' => $term_id, 'title' => $term_value])
                                     ->get();
                    $taxonomy_term->originalPath($taxonomy_term->path());
                    $taxonomy_term->save();
                    $this->localized_cache->setTaxonomyTerm($term_id, $taxonomy, $taxonomy_term);
                }

                // Now we'll swap the entered value with the ID.
                $data[$field][$term_key] = $term_id;

                // Flag as updated so we can save the file later.
                $updated = true;
            }
        }

        // If the data was updated we'll need to re-save it.
        if ($updated) {
            $content = array_get($data, 'content');
            unset($data['content']); // remove it so it doesnt get written to file
            $contents = YAML::dump($data, $content);
            File::disk('content')->put($path, $contents);
            $data['content'] = $content; // put it back in the right place
        }

        return $data;
    }

    /**
     * Create the structure for a page
     *
     * @param string $url
     * @param string $path
     * @return array
     */
    private function createPageStructure($url, $path)
    {
        return [
            'parent' => ($url == '/') ? null : URL::parent($url),
            'depth'  => ($url == '/') ? 0 : substr_count($url, '/'),
            'status' => Path::status($path)
        ];
    }

    /**
     * Take an array of files and return only the files for the current locale
     *
     * @param array|\Illuminate\Support\Collection $files
     * @return array
     */
    private function getLocalizedFiles($files)
    {
        if ($files instanceof Collection) {
            $files = $files->toArray();
        }

        // No other locales? Job is done.
        if (empty($this->other_locales)) {
            return $files;
        }

        $other_locale_regex = '#/(' . implode($this->other_locales, '|') . ')(\.|/)#';
        $locale_regex = '#/' . $this->active_locale . '(\.|/)#';

        foreach ($files as $key => $file) {
            // Remove locale-namespaced files and folders belonging to other locales
            // ie. If we're using en, remove any fr and de files
            if (preg_match($other_locale_regex, $file)) {
                unset($files[$key]);
            }

            // Remove default locale file when there is a locale override
            // ie. Remove /blog/index.md if there is a /blog/fr.index.md
            // and remove /blog/1.post.md if there is a /blog/fr/1.post.md
            $default = preg_replace($locale_regex, '/', $file);
            if ($default !== $file) {
                $files = array_flip($files);
                unset($files[$default]);
                $files = array_flip($files);
            }

            // Ignore the default file if we're not on the default locale
            if ($file == $default && $this->localized_cache->getLocale() !== $this->default_locale) {
                $files = array_flip($files);
                unset($files[$file]);
                $files = array_flip($files);
            }
        }

        return array_values($files);
    }

    /**
     * Remove deleted pages
     */
    private function removeDeletedPages()
    {
        $files = $this->getLocalizedFiles(
            collect_files($this->cache->getDeletedPageFiles())->rejectByExtension('yaml')->all()
        );

        foreach ($files as $path) {
            $url = URL::buildFromPath($path);
            $uuid = $this->localized_cache->getPageUuid($url);

            $this->localized_cache->removePage($uuid);
            $this->localized_cache->removePageUrl($url);
            $this->localized_cache->removePageUuid($uuid);
            $this->localized_cache->removeStructure($url);

            if ($this->active_locale !== $this->default_locale) {
                $this->localized_cache->removeLocalizedPageSlug($uuid);
            }
        }
    }

    private function removeDeletedPageFolders()
    {
        $files = $this->getLocalizedFiles(
            collect_files($this->cache->getDeletedPageFiles())->filterByExtension('yaml')->all()
        );

        foreach ($files as $path) {
            $path = Path::directory(Path::clean(preg_replace('/site\/content\/pages\//', '', $path)));
            $this->localized_cache->removePageFolder($path);
        }
    }

    /**
     * Remove deleted entries
     */
    private function removeDeletedEntries()
    {
        $files = $this->getLocalizedFiles($this->cache->getDeletedEntryFiles());

        foreach ($files as $path) {
            list($folder, $slug) = $this->getFolderAndSlugFromPath($path);

            $str = $folder . '/' . $slug;

            $uuid = $this->localized_cache->getEntryUuid($str);

            $this->localized_cache->removeEntry($uuid, $folder);
            $this->localized_cache->removeEntryPath($str);
            $this->localized_cache->removeEntryUuid($uuid);

            if ($this->active_locale !== $this->default_locale) {
                $this->localized_cache->removeLocalizedEntrySlug($uuid);
            }
        }
    }

    private function removeDeletedCollections()
    {
        $all_files = $this->cache->getFiles();

        $files = $this->cache->getDeletedCollectionFiles();

        // Get all the collections that have had files removed from them
        $collections = [];
        foreach ($files as $path) {
            $collections[] = Path::directory($path);
        }
        $collections = array_unique($collections);

        // Loop over them and check if there are any existing files
        foreach ($collections as $collection) {
            $collection_files = $all_files->filter(function($path) use ($collection) {
                return Str::startsWith($path, $collection);
            });

            // No more files remaining? Remove the collection.
            if ($collection_files->isEmpty()) {
                $this->localized_cache->removeCollection(basename($collection));
            }
        }
    }

    /**
     * Remove deleted taxonomy terms
     */
    private function removeDeletedTaxonomyTerms()
    {
        $files = $this->getLocalizedFiles($this->cache->getDeletedTaxonomyFiles());

        foreach ($files as $path) {
            list($folder, $slug) = $this->getFolderAndSlugFromPath($path);

            $str = $folder . '/' . $slug;

            $uuid = $this->localized_cache->getTaxonomyUuid($str);

            $this->localized_cache->removeTaxonomyTerm($uuid, $folder);
            $this->localized_cache->removeTaxonomyPath($str);
            $this->localized_cache->removeTaxonomyUuid($uuid);

            if ($this->active_locale !== $this->default_locale) {
                $this->localized_cache->removeLocalizedTaxonomySlug($uuid);
            }
        }
    }

    private function removeDeletedTaxonomies()
    {
        $all_files = $this->cache->getFiles();

        $files = $this->cache->getDeletedTaxonomyGroupFiles();

        // Get all the groups that have had files removed from them
        $groups = [];
        foreach ($files as $path) {
            $groups[] = Path::directory($path);
        }
        $groups = array_unique($groups);

        // Loop over them and check if there are any existing files
        foreach ($groups as $group) {
            $group_files = $all_files->filter(function($path) use ($group) {
                return Str::startsWith($path, $group);
            });

            // No more files remaining? Remove the group.
            if ($group_files->isEmpty()) {
                $this->localized_cache->removeTaxonomy(basename($group));
            }
        }
    }

    /**
     * Remove deleted globals
     */
    private function removeDeletedGlobals()
    {
        $files = $this->getLocalizedFiles($this->cache->getDeletedGlobalsFiles());

        foreach ($files as $path) {
            $slug = pathinfo($path)['filename'];

            $uuid = $this->localized_cache->getGlobalUuid($slug);

            $this->localized_cache->removeGlobal($uuid);
            $this->localized_cache->removeGlobalPath($slug);
            $this->localized_cache->removeGlobalUuid($uuid);
        }
    }

    /**
     * Takes a path to an entry/taxonomy and returns the folder and slug
     *
     * @param string $path
     * @return array
     */
    private function getFolderAndSlugFromPath($path)
    {
        $path = Path::clean(preg_replace('#^(?:collections|taxonomies)/#', '', $path));

        $pi = pathinfo($path);

        $path = $pi['dirname'] . '/' . $pi['filename'];

        $path_parts = explode('/', $path);

        return [reset($path_parts), last($path_parts)];
    }
}
