<?php

namespace Statamic\Stache\File;

use Statamic\API\Path;
use Statamic\API\Pattern;
use Statamic\Contracts\Stache\ContentCache as ContentCacheContract;
use Statamic\Contracts\Stache\LocalizedContentCache as LocalizedContentCacheContract;

class ContentCache implements ContentCacheContract
{
    /**
     * @var array
     */
    private $timestamps;

    /**
     * @var \Illuminate\Support\Collection
     */
    private $files;

    /**
     * @var \Illuminate\Support\Collection
     */
    private $modified_files;

    /**
     * @var \Illuminate\Support\Collection
     */
    private $deleted_files;

    /**
     * @var array
     */
    private $locales;

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
     * @return \Illuminate\Support\Collection
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @param $files \Illuminate\Support\Collection
     */
    public function setFiles($files)
    {
        $this->files = $files;
    }

    /**
     * @return array
     */
    public function getLocales()
    {
        return $this->locales;
    }

    /**
     * @param string|null $locale
     * @return mixed
     */
    public function getLocale($locale = null)
    {
        $locale = $locale ?: site_locale();

        return array_get($this->locales, $locale);
    }

    /**
     * @param string                                          $locale
     * @param \Statamic\Contracts\Stache\LocalizedContentCache $cache
     */
    public function setLocale($locale, LocalizedContentCacheContract $cache)
    {
        $this->locales[$locale] = $cache;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getModifiedFiles()
    {
        return $this->modified_files;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getModifiedEntryFiles()
    {
        $path = 'collections/';

        return $this->modified_files->filter(function($file) use ($path) {
            return Pattern::startsWith($file, $path) && !Pattern::endsWith($file, '.yaml');
        });
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getModifiedCollectionFiles()
    {
        $path = 'collections/';

        return $this->modified_files->filter(function($file) use ($path) {
            return Pattern::startsWith($file, $path);
        });
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getModifiedPageFiles()
    {
        $path = 'pages/';

        return $this->modified_files->filter(function($file) use ($path) {
            return Pattern::startsWith($file, $path);
        });
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getModifiedTaxonomyFiles()
    {
        $path = 'taxonomies/';

        return $this->modified_files->filter(function($file) use ($path) {
            return Pattern::startsWith($file, $path) && !Pattern::endsWith($file, '.yaml');
        });
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getModifiedTaxonomyGroupFiles()
    {
        $path = 'taxonomies/';

        return $this->modified_files->filter(function($file) use ($path) {
            return Pattern::startsWith($file, $path);
        });
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getModifiedGlobalsFiles()
    {
        $path = 'globals/';

        return $this->modified_files->filter(function($file) use ($path) {
            return Pattern::startsWith($file, $path);
        });
    }

    /**
     * @param $files \Illuminate\Support\Collection
     */
    public function setModifiedFiles($files)
    {
        $this->modified_files = $files;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getDeletedFiles()
    {
        return $this->deleted_files;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getDeletedEntryFiles()
    {
        $path = 'collections/';

        return $this->deleted_files->filter(function($file) use ($path) {
            return Pattern::startsWith($file, $path);
        });
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getDeletedCollectionFiles()
    {
        $path = 'collections/';

        return $this->deleted_files->filter(function($file) use ($path) {
            return Pattern::startsWith($file, $path);
        });
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getDeletedPageFiles()
    {
        $path = 'pages/';

        return $this->deleted_files->filter(function($file) use ($path) {
            return Pattern::startsWith($file, $path);
        });
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getDeletedTaxonomyFiles()
    {
        $path = 'taxonomies/';

        return $this->deleted_files->filter(function($file) use ($path) {
            return Pattern::startsWith($file, $path);
        });
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getDeletedTaxonomyGroupFiles()
    {
        $path = 'taxonomies/';

        return $this->deleted_files->filter(function($file) use ($path) {
            return Pattern::startsWith($file, $path);
        });
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getDeletedGlobalsFiles()
    {
        $path = 'globals/';

        return $this->deleted_files->filter(function($file) use ($path) {
            return Pattern::startsWith($file, $path);
        });
    }

    /**
     * @param $files \Illuminate\Support\Collection
     */
    public function setDeletedFiles($files)
    {
        $this->deleted_files = $files;
    }
}
