<?php

namespace Statamic\Assets;

use Statamic\API\Asset;
use Statamic\API\Folder;
use Statamic\API\Str;
use Statamic\API\URL;
use Statamic\API\Fieldset;
use Statamic\API\File;
use Statamic\API\YAML;
use Statamic\API\Parse;
use Statamic\API\Storage;
use Statamic\Assets\File\AssetFolder;
use Statamic\Contracts\Assets\AssetContainer as AssetContainerContract;

abstract class AssetContainer implements AssetContainerContract
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @var string
     */
    protected $uuid;

    /**
     * @var string
     */
    protected $driver = 'local';

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var array
     */
    protected $folders = [];

    /**
     * @var string
     */
    protected $fieldset;

    /**
     * Get or set the ID
     *
     * @param null|string $id
     * @return string
     */
    public function id($id = null)
    {
        if (is_null($id)) {
            return $this->uuid;
        }

        return $this->uuid = $id;
    }

    public function uuid($uuid = null)
    {
        return $this->id($uuid);
    }

    /**
     * Get or set the driver
     *
     * @param  null|string $driver
     * @return string
     */
    public function driver($driver = null)
    {
        if (is_null($driver)) {
            return $this->driver;
        }

        return $this->driver = $driver;
    }

    public function data($data = null)
    {
        if (! is_null($data)) {
            $this->data = $data;
            return;
        }

        if ($this->data) {
            return $this->data;
        }

        $path = 'assets/' . $this->uuid . '/container.yaml';

        $this->data = YAML::parse(Storage::get($path));

        return $this->data;
    }

    /**
     * Get or set the title
     *
     * @param null|string $title
     * @return string
     */
    public function title($title = null)
    {
        if ($title) {
            $this->title = $title;
        }

        return $this->title;
    }

    /**
     * Get or set the path
     *
     * @param null|string $path
     * @return string
     */
    public function path($path = null)
    {
        if ($path) {
            $this->path = $path;
        }

        return $this->path;
    }

    /**
     * Get the full resolved path
     *
     * @return string
     */
    public function resolvedPath()
    {
        return Parse::env($this->path());
    }

    /**
     * Get the URL to this location
     *
     * @return null|string
     */
    public function url($url = null)
    {
        if (! is_null($url)) {
            $this->url = $url;
        }

        if ($this->driver === 'local') {
            $path = 'assets/' . $this->uuid . '/container.yaml';
            $yaml = YAML::parse(Storage::get($path));
            $url = array_get($yaml, 'url');
            return (Str::startsWith($url, '/')) ? URL::prependSiteRoot($url, false) : $url;

        } elseif ($this->driver === 's3') {
            $adapter = File::disk("assets:{$this->uuid()}")->filesystem()->getAdapter();
            return rtrim($adapter->getClient()->getObjectUrl($adapter->getBucket(), '/'), '/');
        }

        throw new \RuntimeException('This driver does not support retrieving URLs');
    }

    /**
     * Get all the assets in this container
     *
     * @return \Statamic\Assets\AssetCollection
     */
    public function assets()
    {
        $assets = [];

        foreach ($this->folders() as $folder) {
            foreach ($folder->assets() as $uuid => $asset) {
                $assets[$uuid] = $asset;
            }
        }

        return new AssetCollection($assets);
    }

    /**
     * Get all the folders in this container
     *
     * @return \Statamic\Contracts\Assets\AssetFolder[]
     */
    public function folders()
    {
        return $this->folders;
    }

    /**
     * Get a single folder in this container
     *
     * @param string $folder
     * @return \Statamic\Contracts\Assets\AssetFolder
     */
    public function folder($folder)
    {
        return array_get($this->folders, $folder);
    }

    /**
     * Check if a folder exists
     *
     * @param string $folder
     * @return bool
     */
    public function folderExists($folder)
    {
        return $this->folder($folder) !== null;
    }

    /**
     * Create a folder
     *
     * @param string $folder
     * @param array  $data
     * @return \Statamic\Assets\File\AssetFolder
     */
    public function createFolder($folder, $data = [])
    {
        return new AssetFolder($this->uuid(), $folder, $data);
    }

    /**
     * Add a folder to this container
     *
     * @param string                                 $name
     * @param \Statamic\Contracts\Assets\AssetFolder $folder
     */
    public function addFolder($name, $folder)
    {
        $this->folders[$name] = $folder;
    }

    /**
     * Remove a folder from this container
     *
     * @param string $name
     */
    public function removeFolder($name)
    {
        unset($this->folders[$name]);
    }

    /**
     * Convert to an array
     *
     * @return array
     */
    public function toArray()
    {
        $data = $this->data();

        $data['id'] = $this->uuid();

        return $data;
    }

    /**
     * Get the URL to edit in the CP
     *
     * @return string
     */
    public function editUrl()
    {
        return cp_route('assets.container.edit', $this->uuid());
    }

    /**
     * Get or set the fieldset to be used by assets in this container
     *
     * @param string $fieldset
     * @return Statamic\Contracts\CP\Fieldset
     */
    public function fieldset($fieldset = null)
    {
        if (is_null($fieldset)) {
            return ($this->fieldset) ? Fieldset::get($this->fieldset) : null;
        }

        if ($fieldset === false) {
            return $this->fieldset = null;
        }

        $this->fieldset = $fieldset;
    }

    /**
     * Sync any new files into assets.
     *
     * @return mixed
     */
    public function sync()
    {
        $disk = Folder::disk('assets:' . $this->uuid());

        $files = $disk->getFilesRecursively('/');

        $assets = [];

        foreach ($files as $path) {
            // Always ignore some files.
            if (in_array(pathinfo($path)['basename'], ['.DS_Store'])) {
                continue;
            }

            if ($this->assetExists($path)) {
                continue;
            }

            $assets[] = $this->createAsset($path);
        }

        return new AssetCollection($assets);
    }

    /**
     * Check if an asset with a given path exists in this container
     *
     * @param string $path
     * @return bool
     */
    public function assetExists($path)
    {
        foreach ($this->assets() as $asset) {
            if (ltrim($asset->path(), '/') === $path) {
                return true;
            }
        }

        return false;
    }

    /**
     * Create an asset in this container
     *
     * @param string $path
     * @return \Statamic\Assets\File\Asset
     */
    public function createAsset($path)
    {
        $pathinfo = pathinfo($path);

        $folder = $pathinfo['dirname'];
        $folder = ($folder === '.') ? '/' : $folder;

        if (! $this->folderExists($folder)) {
            $this->addFolder($folder, $this->createFolder($folder));
        }

        $asset = Asset::create()
                      ->container($this->uuid())
                      ->folder($folder)
                      ->file($pathinfo['basename'])
                      ->get();

        $asset->save();

        return $asset;
    }
}
