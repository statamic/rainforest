<?php

namespace Statamic\Contracts\Stache;

use Statamic\Contracts\Assets\Asset;

interface LocalizedAssetCache
{
    /**
     * @return string
     */
    public function getLocale();

    /**
     * @param string $locale
     */
    public function setLocale($locale);

    /**
     * @param string $uuid
     * @return \Statamic\Contracts\Assets\AssetContainer
     */
    public function getAssetContainer($uuid);

    /**
     * @return \Statamic\Contracts\Assets\AssetContainer[]
     */
    public function getAssetContainers();

    /**
     * @param string $uuid
     * @param \Statamic\Contracts\Assets\AssetContainer $container
     */
    public function createAssetContainer($uuid, $container);

    /**
     * @param string $uuid
     */
    public function removeAssetContainer($uuid);

    /**
     * @param string                               $container_uuid
     * @param \Statamic\Contracts\Assets\AssetFolder $folder
     * @return
     */
    public function createAssetFolder($container_uuid, $folder);

    /**
     * @param string $container_uuid
     * @param string $folder
     * @return
     */
    public function removeAssetFolder($container_uuid, $folder);

    /**
     * @param string $uuid
     * @return \Statamic\Contracts\Assets\Asset
     */
    public function getAsset($uuid);

    /**
     * @param string                           $container_uuid
     * @param string                           $folder
     * @param string                           $uuid
     * @param \Statamic\Contracts\Assets\Asset $asset
     * @param string                           $path
     * @return
     */
    public function setAsset($container_uuid, $folder, $uuid, Asset $asset, $path);

    /**
     * @return \Statamic\Contracts\Assets\AssetFolder[]
     */
    public function getAssets();

    /**
     * @param array $assets
     */
    public function setAssets($assets);
}
