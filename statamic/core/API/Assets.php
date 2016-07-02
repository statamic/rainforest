<?php

namespace Statamic\API;

class Assets
{
    /**
     * @return \Statamic\Contracts\Assets\AssetService
     */
    private static function assets()
    {
        return app('Statamic\Contracts\Assets\AssetService');
    }

    /**
     * Get all assets
     *
     * @return \Statamic\Assets\AssetCollection
     */
    public static function all()
    {
        $containers = collect(self::getContainers());

        return collect_assets($containers->flatMap(function ($container) {
            return $container->assets();
        }));
    }


    /**
     * Get all the asset containers
     *
     * @return \Statamic\Contracts\Assets\AssetContainer[]
     */
    public static function getContainers()
    {
        return collect(self::assets()->getContainers())->sortBy(function($container) {
            return $container->title();
        })->all();
    }

    /**
     * Get an asset container by its ID
     *
     * @param string $uuid
     * @return \Statamic\Contracts\Assets\AssetContainer
     */
    public static function getContainer($uuid)
    {
        if ($uuid) {
            return self::assets()->getContainer($uuid);
        }

        return null;
    }

    /**
     * Get an asset container by its path
     *
     * @param string $path
     * @return \Statamic\Contracts\Assets\AssetContainer
     */
    public static function getContainerByPath($path)
    {
        return collect(self::assets()->getContainers())->filter(function ($value) use($path) {
            return $value->path() === $path;
        })->first();
    }

    /**
     * Create an asset container
     *
     * @return \Statamic\Contracts\Assets\AssetContainer
     */
    public static function createContainer($driver = null)
    {
        return app('Statamic\Contracts\Assets\AssetContainerFactory')->create($driver);
    }
}
