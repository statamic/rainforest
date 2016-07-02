<?php

namespace Statamic\Stache\File;

use Statamic\API\Path;
use Statamic\API\Config;
use Statamic\Contracts\Assets\Asset;
use Statamic\Contracts\Stache\LocalizedAssetCache as LocalizedAssetCacheContract;

class LocalizedAssetCache implements LocalizedAssetCacheContract
{
    /**
     * @var string
     */
    private $locale;

    /**
     * @var \Statamic\Contracts\Assets\AssetContainer[]
     */
    private $assets = [];

    /**
     * @var array
     */
    private $uuids;

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @param string $uuid
     * @return \Statamic\Contracts\Assets\AssetContainer
     */
    public function getAssetContainer($uuid)
    {
        return array_get($this->assets, $uuid);
    }

    /**
     * @return \Statamic\Contracts\Assets\AssetContainer[]
     */
    public function getAssetContainers()
    {
        return $this->assets;
    }

    /**
     * @param string                                    $uuid
     * @param \Statamic\Contracts\Assets\AssetContainer $container
     */
    public function createAssetContainer($uuid, $container)
    {
        $this->assets[$uuid] = $container;
    }

    /**
     * @param string $uuid
     */
    public function removeAssetContainer($uuid)
    {
        unset($this->assets[$uuid]);
    }

    /**
     * @param string $container_uuid
     * @param \Statamic\Contracts\Assets\AssetFolder $folder
     */
    public function createAssetFolder($container_uuid, $folder)
    {
        $this->assets[$container_uuid]->addFolder($folder->path(), $folder);
    }

    /**
     * @param string $container_uuid
     * @param string $folder
     */
    public function removeAssetFolder($container_uuid, $folder)
    {
        // Only remove it if the container exists. It may have already been removed.
        if (isset($this->assets[$container_uuid])) {
            $this->assets[$container_uuid]->removeFolder($folder);
        }
    }

    /**
     * @param string                           $container_uuid
     * @param string                           $folder
     * @param string                           $uuid
     * @param \Statamic\Contracts\Assets\Asset $asset
     * @param string                           $path
     */
    public function setAsset($container_uuid, $folder, $uuid, Asset $asset, $path)
    {
        // We want to organize the assets into separate folders so we can
        // have multiple smaller files instead of one enormous file.
        $this->assets[$container_uuid]->folder($folder)->addAsset($uuid, $asset);

        // Then we will have one array that will hold a mapping of all the
        // UUIDs to container/paths so we can perform faster lookups.
        $this->uuids[$uuid] = $container_uuid . '/' . $path;
    }

    /**
     * @param string $uuid
     * @return \Statamic\Contracts\Assets\Asset
     */
    public function getAsset($uuid)
    {
        if (! $path = array_get($this->uuids, $uuid)) {
            return null;
        }

        // The first segment of the path is the container's uuid.
        list($container_uuid, $path) = explode('/', $path, 2);

        return $this->assets[$container_uuid]->assets()->get($uuid);
    }

    /**
     * @return \Statamic\Assets\File\AssetContainer[]
     */
    public function getAssets()
    {
        return $this->assets;
    }

    /**
     * @param array $assets
     */
    public function setAssets($assets)
    {
        $this->assets = $assets;
    }

    /**
     * @return array
     */
    public function getUuids()
    {
        return $this->uuids;
    }

    /**
     * @param array $uuids
     */
    public function setUuids($uuids)
    {
        $this->uuids = $uuids;
    }
}
