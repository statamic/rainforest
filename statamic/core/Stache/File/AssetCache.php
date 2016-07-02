<?php

namespace Statamic\Stache\File;

use Statamic\Contracts\Stache\LocalizedAssetCache;
use Statamic\Contracts\Stache\AssetCache as AssetCacheContract;

class AssetCache implements AssetCacheContract
{
    /**
     * @var \Statamic\Contracts\Stache\LocalizedAssetCache[]
     */
    private $locales;

    /**
     * @var array
     */
    private $timestamps;

    /**
     * @var \Statamic\FileCollection
     */
    private $files;

    /**
     * @var \Statamic\FileCollection
     */
    private $modified_files;

    /**
     * @var \Statamic\FileCollection
     */
    private $deleted_files;

    /**
     * Whether an update has occurred
     *
     * @var bool
     */
    private $updated = false;

    /**
     * Get or set whether the cache has been updated
     *
     * @param  bool|null  $updated
     * @return boolean
     */
    public function hasBeenUpdated($updated = null)
    {
        if (is_null($updated)) {
            return $this->updated;
        }

        $this->updated = $updated;
    }

    /**
     * Get the localized caches
     *
     * @return \Statamic\Contracts\Stache\LocalizedAssetCache[]
     */
    public function getLocales()
    {
        return $this->locales;
    }

    /**
     * Get a localized cache
     *
     * @param string|null $locale
     * @return \Statamic\Contracts\Stache\LocalizedAssetCache
     */
    public function getLocale($locale = null)
    {
        $locale = $locale ?: site_locale();

        return array_get($this->locales, $locale);
    }

    /**
     * Set a localized cache
     *
     * @param string                                        $locale
     * @param \Statamic\Contracts\Stache\LocalizedAssetCache $cache
     */
    public function setLocale($locale, LocalizedAssetCache $cache)
    {
        $this->locales[$locale] = $cache;
    }

    /**
     * @return array
     */
    public function getTimestamps()
    {
        return $this->timestamps;
    }

    /**
     * @param array $timestamps
     */
    public function setTimestamps($timestamps)
    {
        $this->timestamps = $timestamps;
    }

    /**
     * @param string $path
     * @param int    $timestamp
     */
    public function setTimestamp($path, $timestamp)
    {
        $this->timestamps[$path] = $timestamp;
    }

    /**
     * @param string $path
     */
    public function removeTimestamp($path)
    {
        unset($this->timestamps[$path]);
    }

    /**
     * @return \Statamic\FileCollection
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @param $files \Statamic\FileCollection
     */
    public function setFiles($files)
    {
        $this->files = $files;
    }

    /**
     * @return \Statamic\FileCollection
     */
    public function getModifiedFiles()
    {
        return $this->modified_files;
    }

    /**
     * @param $files \Statamic\FileCollection
     */
    public function setModifiedFiles($files)
    {
        $this->modified_files = $files;
    }

    /**
     * @return \Statamic\FileCollection
     */
    public function getDeletedFiles()
    {
        return $this->deleted_files;
    }

    /**
     * @param $files \Statamic\FileCollection
     */
    public function setDeletedFiles($files)
    {
        $this->deleted_files = $files;
    }
}
