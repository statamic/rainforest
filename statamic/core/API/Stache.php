<?php

namespace Statamic\API;

class Stache
{
    /**
     * @return \Statamic\Contracts\Stache\CacheService
     */
    private static function stache()
    {
        return app('Statamic\Contracts\Stache\CacheService');
    }

    public static function update()
    {
        // Update the cache, but we want to assume we're using the default locale.
        // Once it's done, we'll change it back.
        site_locale(default_locale());

        self::stache()->update();

        site_locale(LOCALE);
    }

    public static function load()
    {
        self::stache()->load();
    }

    /**
     * Does the Stache exist? Has it been created?
     *
     * @return bool
     */
    public static function exists()
    {
        return Cache::get('stache/content/timestamps');
    }

    /**
     * Clear the Stache
     *
     * @todo This would be much simpler if the File cache adapter supported tags
     */
    public static function clear()
    {
        $keys = [
            'assets/timestamps',
            'assets/containers',
            'content/timestamps',
            'users/timestamps',
            'users/groups',
            'users/users'
        ];

        foreach (Config::getLocales() as $l) {
            $keys = array_merge($keys, [
                "content/$l/collection_folders",
                "content/$l/globals",
                "content/$l/localized_slugs",
                "content/$l/localized_urls",
                "content/$l/page_folders",
                "content/$l/pages",
                "content/$l/paths",
                "content/$l/structure",
                "content/$l/taxonomies",
                "content/$l/uuids",
                "assets/$l/uuids",
            ]);

            // iterate over collections
            foreach (Content::collectionNames() as $c) {
                $keys[] = "content/$l/collections/$c";
            }

            // iterate over taxonomies
            foreach (Content::taxonomyNames() as $t) {
                $keys[] = "content/$l/terms/$t";
            }

            // iterate over asset containers
            foreach (array_keys(Assets::getContainers()) as $a) {
                $keys[] = "assets/$l/containers/$a/data";
            }
        }

        foreach ($keys as $key) {
            Cache::forget('stache/'.$key);
        }
    }
}
