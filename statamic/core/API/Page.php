<?php

namespace Statamic\API;

class Page
{
    /**
     * @return \Statamic\Contracts\Data\Pages\PageFactory
     */
    private static function factory()
    {
        return app('Statamic\Contracts\Data\Pages\PageFactory');
    }

    /**
     * @param $url
     * @return \Statamic\Contracts\Data\Pages\PageFactory
     */
    public static function create($url)
    {
        return self::factory()->create($url);
    }

    /**
     * Get a page by its URL
     *
     * @param string      $url
     * @param string|null $locale
     * @return \Statamic\Contracts\Data\Pages\Page
     */
    public static function getByUrl($url, $locale = null)
    {
        return Content::pageRaw($url, $locale);
    }

    /**
     * Get a page by UUID
     *
     * @param string $uuid
     * @param null   $locale
     * @return \Statamic\Contracts\Data\Pages\Page
     */
    public static function getByUuid($uuid, $locale = null)
    {
        return Content::uuidRaw($uuid, $locale);
    }
}
