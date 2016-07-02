<?php

namespace Statamic\API;

class Entry
{
    /**
     * @return \Statamic\Contracts\Data\Entries\EntryFactory
     */
    private static function factory()
    {
        return app('Statamic\Contracts\Data\Entries\EntryFactory');
    }

    /**
     * @param string $slug
     * @return \Statamic\Contracts\Data\Entries\EntryFactory
     */
    public static function create($slug)
    {
        return self::factory()->create($slug);
    }

    /**
     * Get an entry from a collection, by its slug
     *
     * @param string       $collection
     * @param string       $slug
     * @param string|null  $locale
     * @return \Statamic\Contracts\Data\Entries\Entry
     */
    public static function getFromCollection($collection, $slug, $locale = null)
    {
        return Content::entryRaw($slug, $collection, $locale);
    }

    /**
     * Get an entry by UUID
     *
     * @param string $uuid
     * @param null   $locale
     * @return \Statamic\Contracts\Data\Entries\Entry
     */
    public static function getByUuid($uuid, $locale = null)
    {
        return Content::uuidRaw($uuid, $locale);
    }
}
