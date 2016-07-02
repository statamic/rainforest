<?php

namespace Statamic\Assets\File;

use Statamic\API\Path;
use Statamic\API\YAML;
use Statamic\API\Assets;
use Statamic\API\Str;
use Statamic\API\Folder;
use Statamic\API\Storage;
use Statamic\Assets\AssetCollection;
use Illuminate\Contracts\Support\Arrayable;
use Statamic\Contracts\Assets\AssetFolder as AssetFolderContract;

class AssetFolder implements AssetFolderContract, Arrayable
{
    /**
     * @var string
     */
    protected $container;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var \Statamic\Assets\AssetCollection
     */
    protected $assets;

    /**
     * @var \Carbon\Carbon
     */
    protected $last_modified;

    /**
     * @var array
     */
    protected $data;

    /**
     * @param string     $path
     * @param array|null $data
     */
    public function __construct($container_uuid, $path, $data = [])
    {
        $this->container = $container_uuid;
        $this->path   = $path;
        $this->data   = $data;
        $this->assets = new AssetCollection;
    }

    /**
     * @param string     $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return array_get($this->data, $key, $default);
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Get or set the data
     *
     * @param array|null $data
     * @return mixed
     */
    public function data($data = null)
    {
        if (! $data) {
            return $this->data;
        }

        $this->data = $data;
    }

    /**
     * @param string|null $path
     * @return string
     */
    public function path($path = null)
    {
        if (is_null($path)) {
            return $this->path;
        }

        $this->path = $path;
    }

    /**
     * @return string
     */
    public function resolvedPath()
    {
        return Path::tidy($this->container()->resolvedPath() . '/' . $this->path());
    }

    /**
     * Get the basename of the folder
     *
     * @return string
     */
    public function basename()
    {
        return basename($this->path());
    }

    /**
     * @return string
     */
    public function title()
    {
        return $this->get('title', pathinfo($this->path())['filename']);
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->assets->count();
    }

    /**
     * @return \Statamic\Assets\AssetCollection
     */
    public function assets()
    {
        return $this->assets;
    }

    /**
     * @param string                         $key
     * @param \Statamic\Contracts\Data\Asset $asset
     */
    public function addAsset($key, $asset)
    {
        $this->assets->put($key, $asset);
    }

    /**
     * @param string $key
     */
    public function removeAsset($key)
    {
        $this->assets->forget($key);
    }

    /**
     * @return \Carbon\Carbon
     */
    public function lastModified()
    {
        $date = null;

        foreach ($this->assets() as $asset) {
            $modified = $asset->getLastModified();

            if ($date) {
                if ($modified->gt($date)) {
                    $date = $modified;
                }
            } else {
                $date = $modified;
            }
        }

        return $date;
    }

    /**
     * @return mixed
     */
    public function save()
    {
        $path = 'assets/' . $this->container()->uuid() . '/' . $this->path() . '/folder.yaml';

        // Remove assets first to ensure they are at end of the array.
        // This is for no reason other than it's much nicer to be able to see the folder's data first.
        unset($this->data['assets']);

        $this->data['assets'] = $this->assets()->map(function($asset) {
            return $asset->data();
        })->all();

        Storage::put($path, YAML::dump($this->data()));
    }

    /**
     * Delete the folder
     *
     * @return mixed
     */
    public function delete()
    {
        $storage_folder = 'assets/' . $this->container()->uuid() . '/' . $this->path();

        Folder::disk('storage')->delete($storage_folder);

        Folder::delete($this->resolvedPath());
    }

    /**
     * Get the container where this folder is located
     *
     * @return \Statamic\Contracts\Assets\AssetContainer
     */
    public function container()
    {
        return Assets::getContainer($this->container);
    }

    /**
     * Get the nested folders
     *
     * @param int $depth
     * @return \Statamic\Contracts\Assets\AssetFolder[]
     */
    public function folders($depth = 1)
    {
        return collect($this->container()->folders())->reject(function($folder) use ($depth) {
            // We don't want the current folder in the list.
            if ($folder->path() === $this->path()) {
                return true;
            }

            // From here, we'll keep track of whether we want to reject.
            $reject = false;

            // Only keep nested pages.
            if ($this->path() !== '/') {
                if (! Str::startsWith($folder->path(), $this->path())) {
                    $reject = true;
                }
            }

            // Keep pages that meet the depth requirement
            if ($this->path() === '/') {
                $reject = substr_count($folder->path(), '/') >= $depth;
            } else {
                $slash_diff = substr_count($folder->path(), '/') - substr_count($this->path(), '/');
                if ($slash_diff > $depth) {
                    $reject = true;
                }
            }

            return $reject;

        })->values()->all();

        return $folders;
    }

    /**
     * Get the parent folder
     *
     * @return null|\Statamic\Contracts\Assets\AssetFolder
     */
    public function parent()
    {
        if ($this->path() === '/') {
            return null;
        }

        $path = Path::popLastSegment($this->path());
        $path = ($path === '') ? '/' : $path;

        return $this->container()->folder($path);
    }

    /**
     * Create a nested folder
     *
     * @param string $basename
     * @return \Statamic\Contracts\Assets\AssetFolder
     */
    public function createFolder($basename)
    {
        $path = ltrim(Path::assemble($this->path(), $basename), '/');

        $folder = new AssetFolder($this->container()->uuid(), $path);

        return $folder;
    }

    public function toArray()
    {
        return [
            'title' => $this->title(),
            'path' => $this->path(),
            'parent_path' => ($this->parent()) ? $this->parent()->path() : null
        ];
    }

    /**
     * Get the URL to edit this in the CP
     *
     * @return string
     */
    public function editUrl()
    {
        //
    }
}
