<?php

namespace Statamic\Assets\File;

use Carbon\Carbon;
use Statamic\API\Str;
use Statamic\API\URL;
use Statamic\API\Path;
use Statamic\API\YAML;
use Statamic\API\Event;
use Statamic\API\Image;
use Statamic\Data\File\Data;
use Statamic\API\Assets;
use Statamic\API\Config;
use Statamic\API\Helper;
use Statamic\API\Storage;
use Statamic\API\Fieldset;
use Statamic\API\File;
use Statamic\Exceptions\UuidExistsException;
use Statamic\Contracts\Assets\Asset as AssetContract;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Asset extends Data implements AssetContract
{
    /**
     * @var string
     */
    protected $locale;

    /**
     * @var string
     */
    protected $container;

    /**
     * @var string
     */
    protected $folder;

    /**
     * @var string
     */
    protected $basename;

    /**
     * @var string
     */
    protected $uuid;

    /**
     * @param array $locale
     * @param array $data
     */
    public function __construct($locale, $data = [])
    {
        parent::__construct($data);

        $this->locale = $locale ?: site_locale();
    }

    /**
     * Get the driver this asset's container uses
     *
     * @return string
     */
    public function driver()
    {
        return $this->container()->driver();
    }

    /**
     * Get the container's filesystem disk instance
     *
     * @return Statamic\Filesystem\FileAccessor
     */
    public function disk()
    {
        return File::disk('assets:' . $this->container()->uuid());
    }

    /**
     * Get or set the data
     *
     * @param array|null $data
     * @return array
     */
    public function data($data = null)
    {
        if (! is_null($data)) {
            $this->data = $data;

            return;
        }

        // If we're in the default locale, we need to store the filename.
        $data = ($this->locale === Config::getDefaultLocale())
            ? ['file' => $this->basename()]
            : [];

        $data = array_merge($data, $this->data);

        // Ensure the uuid isn't in the array.
        unset($data['id']);

        return $data;
    }

    /**
     * @param string|null $folder
     * @return \Statamic\Contracts\Assets\AssetFolder
     */
    public function folder($folder = null)
    {
        if (is_null($folder)) {
            return $this->container()->folder($this->folder);
        }

        $this->folder = $folder;
    }

    /**
     * @return string
     */
    public function filename()
    {
        return pathinfo($this->basename())['filename'];
    }

    /**
     * @param string|null $basename
     * @return string
     */
    public function basename($basename = null)
    {
        if (is_null($basename)) {
            return $this->basename;
        }

        $this->basename = $basename;
    }

    /**
     * Get or set the path to the data
     *
     * @param string|null $path Path to set
     * @return mixed
     */
    public function path($path = null)
    {
        if (is_null($path)) {
            return $this->getPath();
        }

        $this->setPath($path);
    }

    /**
     * @return string
     */
    protected function getPath()
    {
        if ($this->path) {
            return $this->path;
        }

        return Path::tidy($this->folder()->path() . '/' . $this->basename());
    }

    public function resolvedPath()
    {
        return Path::tidy($this->folder()->resolvedPath() . '/' . $this->basename());
    }

    /**
     * Get the asset's URL
     *
     * @return string
     * @throws \RuntimeException
     */
    public function url()
    {
        if ($this->driver() === 'local') {
            return URL::encode(Path::tidy($this->container()->url() . '/' . $this->getPath()));

        } elseif ($this->driver() === 's3') {
            $adapter = $this->disk()->filesystem()->getAdapter();
            return URL::encode(URL::tidy($adapter->getClient()->getObjectUrl(
                $adapter->getBucket(),
                $this->container()->path() . '/' . $this->path()
            )));
        }

        throw new \RuntimeException('This driver does not support retrieving URLs.');
    }

    /**
     * Get the asset's absolute URL
     *
     * @return string
     * @throws \RuntimeException
     */
    public function absoluteUrl()
    {
        $url = $this->url();

        if ($this->driver() === 'local') {
            $url = URL::prependSiteUrl($url);
        }

        return $url;
    }

    /**
     * Get either a image URL builder instance, or a URL if passed params.
     *
     * @param null|array $params Optional manipulation parameters to return a string right away
     * @return \Statamic\Contracts\Assets\Manipulation\UrlBuilder|string
     * @throws \Exception
     */
    public function manipulate($params = null)
    {
        return Image::manipulate($this->id());
    }

    /**
     * Is this asset an image?
     *
     * @return bool
     */
    public function isImage()
    {
        return (in_array(strtolower($this->dataType()), ['jpg', 'jpeg', 'png', 'gif']));
    }

    /**
     * @return string
     */
    public function extension()
    {
        return Path::extension($this->path());
    }

    /**
     * @return \Carbon\Carbon
     */
    public function lastModified()
    {
        return Carbon::createFromTimestamp($this->disk()->lastModified($this->path()));
    }

    /**
     * Save the file
     */
    public function save()
    {
        $storage_file = Path::assemble(
            'assets',
            $this->container()->uuid(),
            $this->folder()->path(),
            'folder.yaml'
        );

        $this->ensureId();

        $yaml = YAML::parse(Storage::get($storage_file));

        array_set($yaml, 'assets.' . $this->id(), $this->data());

        Storage::put($storage_file, YAML::dump($yaml));
    }

    /**
     * Delete the data
     *
     * @return mixed
     */
    public function delete()
    {
        // First we need to remove the asset from the array in folder.yaml
        // and the corresponding localized versions, if applicable.
        $this->folder()->removeAsset($this->id());
        $this->folder()->save();

        // Also, delete the actual file
        $this->disk()->delete($this->path());
    }

    /**
     * Get or set the container where this asset is located
     *
     * @param string $uuid  UUID of the container
     * @return \Statamic\Contracts\Assets\AssetContainer
     */
    public function container($uuid = null)
    {
        if ($uuid) {
            $this->container = $uuid;
        } else {
            return Assets::getContainer($this->container);
        }
    }

    /**
     * Rename the data
     */
    protected function rename()
    {
        // TODO: Implement delete() method.
    }

    public function supplement()
    {
    }

    /**
     * Get the asset's dimensions
     *
     * @return array  An array in the [width, height] format
     */
    public function dimensions()
    {
        if (! $this->isImage()) {
            return [null, null];
        }

        if ($this->driver() === 'local') {
            $path = Path::assemble($this->disk()->filesystem()->getAdapter()->getPathPrefix(), $this->path());
            return getimagesize($path);
        } elseif ($this->driver() === 's3') {
            return getimagesize($this->url());
        }
    }

    /**
     * Get the asset's width
     *
     * @return int|null
     */
    public function width()
    {
        return array_get($this->dimensions(), 0);
    }

    /**
     * Get the asset's height
     *
     * @return int|null
     */
    public function height()
    {
        return array_get($this->dimensions(), 1);
    }

    /**
     * Convert to an array
     *
     * @return array
     */
    public function toArray()
    {
        $array = parent::toArray();

        unset($array['content'], $array['content_raw']);

        $size = $this->disk()->size($this->path());
        $kb = number_format($size / 1024, 2);
        $mb = number_format($size / 1048576, 2);
        $gb = number_format($size / 1073741824, 2);

        $extra = [
            'uuid'      => $this->id(), // @todo remove
            'id'        => $this->id(),
            'title'     => $this->get('title', $this->filename()),
            'url'       => $this->url(),
            'permalink' => $this->absoluteUrl(),
            'path'      => $this->path(),
            'filename'  => $this->filename(),
            'basename'  => $this->basename(),
            'extension' => $this->extension(),
            'is_image'  => $this->isImage(),
            'is_asset'  => true,
            'size'           => $this->disk()->sizeHuman($this->path()),
            'size_bytes'     => $size,
            'size_kilobytes' => $kb,
            'size_megabytes' => $mb,
            'size_gigabytes' => $gb,
            'size_b'         => $size,
            'size_kb'        => $kb,
            'size_mb'        => $mb,
            'size_gb'        => $gb,
            'width'          => $this->width(),
            'height'          => $this->height(),
            'last_modified'  => (string) $this->lastModified(),
            'last_modified_timestamp' => $this->lastModified()->timestamp,
            'last_modified_instance'  => $this->lastModified(),
            'fieldset' => $this->fieldset()->name()
        ];

        return array_merge($array, $extra);
    }

    /**
     * Get or set the ID
     *
     * @param string|bool|null $id
     * @return string
     */
    public function id($id = null)
    {
        if (is_null($id)) {
            return $this->uuid;
        }

        $this->setId($id);
    }

    /**
     * Set the UUID
     *
     * @param string|bool $id
     * @throws \Statamic\Exceptions\UuidExistsException
     */
    protected function setId($id)
    {
        if ($this->id()) {
            throw new UuidExistsException('Data already has a UUID');
        }

        // If true is passed in, we'll generate a UUID. Otherwise just use what was passed.
        $this->uuid = ($id === true) ? Helper::makeUuid() : $id;
    }

    /**
     * Upload a file
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     */
    public function upload(UploadedFile $file)
    {
        $basename  = $file->getClientOriginalName();
        $filename  = pathinfo($basename)['filename'];
        $ext       = $file->getClientOriginalExtension();

        $directory = $this->folder()->path();
        $path      = Path::tidy($directory . '/' . $filename . '.' . $ext);

        // If the file exists, we'll append a timestamp to prevent overwriting.
        if ($this->disk()->exists($path)) {
            $basename = $filename . '-' . time() . '.' . $ext;
            $path = Str::removeLeft(Path::assemble($directory, $basename), '/');
        }

        $stream = fopen($file->getRealPath(), 'r+');
        $this->disk()->put($path, $stream);
        fclose($stream);

        Event::fire('asset.uploaded', $path);

        $this->basename($basename);
    }

    /**
     * Get or set the fieldset
     *
     * @param string|null $fieldset
     * @return \Statamic\CP\Fieldset
     */
    public function fieldset($fieldset = null)
    {
        if (! is_null($fieldset)) {
            throw new \Exception('You cannot set an asset fieldset.');
        }

        // Check the container
        if ($fieldset = $this->container()->fieldset()) {
            return $fieldset;
        }

        // Then the default asset fieldset
        return Fieldset::get(Config::get('theming.default_asset_fieldset'));
    }

    /**
     * Get the URL to edit in the CP
     *
     * @return string
     */
    public function editUrl()
    {
        return cp_route('asset.edit', $this->id());
    }
}
