<?php

namespace Statamic\API;

class Entries
{
    /**
     * Get entries from a collection
     *
     * @param string      $collection
     * @param array|null  $slugs
     * @param string|null $locale
     * @param bool        $fallback
     * @return \Statamic\Data\ContentCollection
     */
    public static function getFromCollection($collection, $slugs = null, $locale = null, $fallback = false)
    {
        $entries = Content::entries($collection, $locale, $fallback);

        if ($slugs) {
            $slugs = Helper::ensureArray($slugs);

            $entries = $entries->filter(function ($entry) use ($slugs) {
                return in_array($entry->getSlug(), $slugs);
            });
        }

        return $entries;
    }

    /**
     * @param $slug
     * @return \Statamic\Contracts\Data\Entries\CollectionFolder
     */
    public static function createCollection($slug)
    {
        /** @var \Statamic\Contracts\Data\Entries\CollectionFolder $collection */
        $collection = app('Statamic\Contracts\Data\Entries\CollectionFolder');

        $collection->path($slug);

        return $collection;
    }
}
