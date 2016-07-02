<?php

namespace Statamic\API;

class Globals
{
    /**
     * @return \Statamic\Contracts\Data\Globals\GlobalFactory
     */
    private static function factory()
    {
        return app('Statamic\Contracts\Data\Globals\GlobalFactory');
    }

    /**
     * @param string $slug
     * @return \Statamic\Contracts\Data\Globals\GlobalFactory
     */
    public static function create($slug)
    {
        return self::factory()->create($slug);
    }

    /**
     * Get a global by UUID
     *
     * @param string $uuid
     * @param null   $locale
     * @return \Statamic\Contracts\Data\Globals\GlobalContent
     */
    public static function getByUuid($uuid, $locale = null)
    {
        return Content::uuidRaw($uuid, $locale);
    }

    /**
     * Get a global by its slug
     *
     * @param string      $slug
     * @param string|null $locale
     * @param bool|false  $fallback
     * @return \Statamic\Contracts\Data\Globals\GlobalContent
     */
    public static function getBySlug($slug, $locale = null, $fallback = false)
    {
        return Content::globals($slug, $locale, $fallback)->first();
    }

    /**
     * @param null $locale
     * @param bool $fallback
     * @return \Statamic\Data\Globals\GlobalCollection
     */
    public static function getAll($locale = null, $fallback = false)
    {
        return Content::globals(null, $locale, $fallback);
    }
}
