<?php

namespace Statamic\API;

use Statamic\Assets\AssetFactory;

class Asset
{
    /**
     * @return \Statamic\Contracts\Assets\AssetService
     */
    private static function assets()
    {
        return app('Statamic\Contracts\Assets\AssetService');
    }

    /**
     * @return \Statamic\Assets\AssetFactory
     */
    private static function factory()
    {
        return new AssetFactory;
    }

    /**
     * @param string|null $uuid
     * @return \Statamic\Assets\AssetFactory
     */
    public static function create($uuid = null)
    {
        return self::factory()->create($uuid);
    }

    /**
     * Get a raw asset by its UUID
     *
     * @param string      $uuid
     * @param string|null $locale
     * @return \Statamic\Contracts\Assets\Asset
     */
    public static function uuidRaw($uuid, $locale = null)
    {
        return self::assets()->getUuid($uuid, $locale);
    }

    /**
     * Get an asset by its UUID
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
     * Get an asset by its path
     *
     * @param string      $path
     * @return Asset
     */
    public static function path($path)
    {
        return Assets::all()->filter(function ($asset) use ($path) {
            return $asset->resolvedPath() === $path;
        })->first();
    }
}
