<?php

namespace Statamic\Stache\Listeners;

use Statamic\Contracts\Stache\Cache;
use Statamic\Contracts\Data\Pages\Page;
use Statamic\Contracts\Data\Entries\Entry;
use Statamic\Contracts\Data\Taxonomies\Term;
use Statamic\Contracts\Data\Content\Content;
use Statamic\Contracts\Data\Globals\GlobalContent;

class UpdateContent
{
    /**
     * @var Cache
     */
    private $stache;

    /**
     * Create a new listener
     *
     * @param Cache $stache
     */
    public function __construct(Cache $stache)
    {
        $this->stache = $stache;
    }

    /**
     * Handle the event.
     *
     * @param Content $content
     * @return void
     */
    public function handle(Content $content)
    {
        // If the stache isn't ready, just move on.
        if (! $cache = $this->stache->getContent()) {
            return;
        }

        $cache = $cache->getLocale($content->locale());

        if ($content instanceof Page) {
            $cache->setPage($content->id(), $content);
            $cache->setPageUuid($content->id(), $content->urlPath());

        } elseif ($content instanceof Entry) {
            $ref = $content->collectionName().'/'.$content->slug();
            $cache->setEntry($content->id(), $content->collectionName(), $content);
            $cache->setEntryUuid($content->id(), $ref);
            $cache->setEntryPath($ref, $content->path());

        } elseif ($content instanceof Term) {
            $ref = $content->taxonomyName().'/'.$content->slug();
            $cache->setTaxonomyTerm($content->id(), $content->taxonomyName(), $content);
            $cache->setTaxonomyUuid($content->id(), $ref);
            $cache->setTaxonomyPath($ref, $content->path());

        } elseif ($content instanceof GlobalContent) {
            $cache->setGlobal($content->id(), $content);
            $cache->setGlobalUuid($content->id(), $content->slug());
            $cache->setGlobalPath($content->slug(), $content->path());
        }
    }
}