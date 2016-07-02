<?php

namespace Statamic\Stache\File;

use Statamic\API\Path;
use Statamic\API\YAML;
use Statamic\API\Asset;
use Statamic\API\Assets;
use Statamic\API\Config;
use Statamic\API\Str;
use Statamic\API\File;
use Illuminate\Support\Collection;
use Statamic\Assets\File\AssetFolder;
use Statamic\Contracts\Stache\AssetCache as AssetCacheContract;
use Statamic\Contracts\Stache\LocalizedAssetCache as LocalizedAssetCacheContract;
use Statamic\Contracts\Stache\LocalizedAssetCacheUpdater as LocalizedAssetCacheUpdaterContract;

class LocalizedAssetCacheUpdater implements LocalizedAssetCacheUpdaterContract
{
    /**
     * @var \Statamic\Contracts\Stache\AssetCache
     */
    private $cache;

    /**
     * @var \Statamic\Contracts\Stache\LocalizedAssetCache
     */
    private $localized_cache;

    /**
     * @var array
     */
    private $locales;

    /**
     * @var string
     */
    private $active_locale;

    /**
     * @var string
     */
    private $default_locale;

    /**
     * @var array
     */
    private $other_locales;

    /**
     * @param \Statamic\Contracts\Stache\AssetCache $cache
     */
    public function __construct(AssetCacheContract $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param \Statamic\Contracts\Stache\LocalizedAssetCache $localized_cache
     * @return \Statamic\Contracts\Stache\LocalizedAssetCache
     */
    public function update(LocalizedAssetCacheContract $localized_cache)
    {
        $this->localized_cache = $localized_cache;

        $this->setUpLocaleData();

        $this->removeDeletedContainers();
        $this->removeDeletedFolders();

        $this->updateAssets();

        return $this->localized_cache;
    }

    /**
     * Set up the locale data
     */
    private function setUpLocaleData()
    {
        $this->locales = Config::getLocales();
        $this->active_locale  = $this->localized_cache->getLocale();
        $this->default_locale = reset($this->locales);
        $this->other_locales  = array_diff($this->locales, [$this->active_locale]);
    }

    private function updateAssets()
    {
        // Update containers
        $containers = $this->cache->getModifiedFiles()->filter(function($path) {
            return Str::endsWith($path, 'container.yaml');
        });
        if (! $containers->isEmpty()) {
            foreach ($containers as $container_path) {
                $this->cache->setTimestamp($container_path, File::disk('storage')->lastModified($container_path));
                $container_data = YAML::parse(File::disk('storage')->get($container_path));
                preg_match('#^assets\/(.*)\/#', $container_path, $matches);
                $uuid = $matches[1];
                if (! $container = $this->localized_cache->getAssetContainer($uuid)) {
                    $driver = array_get($container_data, 'driver', 'local');
                    $container = Assets::createContainer($driver);
                }
                $container->uuid($uuid);
                $container->path(array_get($container_data, 'path', '/'));
                $container->title(array_get($container_data, 'title'));
                $container->fieldset(array_get($container_data, 'fieldset', false));
                $this->localized_cache->createAssetContainer($uuid, $container);
            }
        }

        // Look in every folder's yaml file
        foreach ($this->getLocalizedFiles($this->cache->getModifiedFiles()) as $path) {
            if (Str::endsWith($path, 'container.yaml')) {
                continue;
            }

            $folder = preg_replace('#^assets#', '', Path::directory($path));
            $folder = Str::ensureRight(ltrim($folder, '/'), '/');

            list($container_uuid, $folder) = explode('/', $folder, 2);

            $folder = ($folder == '') ? '/' : rtrim($folder, '/');

            $this->cache->setTimestamp($path, File::disk('storage')->lastModified($path));

            $yaml = YAML::parse(File::disk('storage')->get($path));
            $this->localized_cache->createAssetFolder(
                $container_uuid,
                new AssetFolder($container_uuid, $folder, $yaml)
            );

            if ($this->active_locale !== $this->default_locale) {
                $default_path = str_replace("{$this->active_locale}.folder.yaml", 'folder.yaml', $path);
                $default_yaml = YAML::parse(File::disk('storage')->get($default_path));
            } else {
                $default_yaml = $yaml;
            }

            foreach (array_get($yaml, 'assets') as $uuid => $data) {
                $data['folder'] = $folder;

                // Only the default yaml file contains filenames
                $data['file'] = array_get($default_yaml, 'assets.'.$uuid.'.file');

                $this->localized_cache->setAsset(
                    $container_uuid,
                    $folder,
                    $uuid,
                    $this->createAsset($container_uuid, $uuid, $data),
                    $container_uuid . '/' . $folder . '/' . $data['file']
                );
            }
        }
    }

    private function removeDeletedContainers()
    {
        $assets_path = rtrim(Path::makeRelative(site_storage_path('assets')), '/');

        foreach ($this->getLocalizedFiles($this->cache->getDeletedFiles()) as $path) {
            if (! Str::endsWith($path, 'container.yaml')) {
                continue;
            }

            preg_match('#^assets\/(.*)\/#', $path, $matches);
            $uuid = $matches[1];

            $this->cache->removeTimestamp($path);
            $this->localized_cache->removeAssetContainer($uuid);
        }
    }

    private function removeDeletedFolders()
    {
        $assets_path = rtrim(Path::makeRelative(site_storage_path('assets')), '/');

        foreach ($this->getLocalizedFiles($this->cache->getDeletedFiles()) as $path) {
            if (Str::endsWith($path, 'container.yaml')) {
                continue;
            }

            $folder = preg_replace('#^'.$assets_path.'#', '', Path::directory($path));
            $folder = ltrim($folder, '/');
            $folder = ($folder == '') ? '/' : $folder;

            $this->cache->removeTimestamp($path);

            $parts = explode('/', $folder, 2);
            $container = $parts[0];
            $folder = array_get($parts, 1, '/');

            $this->localized_cache->removeAssetFolder($container, $folder);
        }
    }

    /**
     * Create an Asset object from some data
     *
     * @param string $uuid
     * @param $data
     * @return \Statamic\Assets\File\Asset
     */
    private function createAsset($container_uuid, $uuid, $data)
    {
        $file = $data['file'];
        $folder = $data['folder'];
        unset($data['file'], $data['folder']);

        $asset = Asset::create($uuid)
            ->locale($this->active_locale)
            ->file($file)
            ->container($container_uuid)
            ->folder($folder)
            ->with($data)
            ->get();

        return $asset;
    }

    /**
     * Take an array of files and return only the files for the current locale
     *
     * @param \Statamic\FileCollection $files
     * @return \Statamic\FileCollection
     */
    private function getLocalizedFiles($files)
    {
        if ($files instanceof Collection) {
            $files = $files->all();
        }

        // No other locales? Job is done.
        if (empty($this->other_locales)) {
            return $files;
        }

        $other_locale_regex = '#/(' . implode($this->other_locales, '|') . ')\.#';
        $locale_regex = '#/' . $this->active_locale . '\.#';

        foreach ($files as $key => $file) {
            // Remove locale-namespaced files and folders belonging to other locales
            // ie. If we're using en, remove any fr and de files
            if (preg_match($other_locale_regex, $file)) {
                unset($files[$key]);
            }

            // Remove default locale file when there is a locale override
            // ie. Remove /blog/index.md if there is a /blog/fr.index.md
            // and remove /blog/1.post.md if there is a /blog/fr/1.post.md
            $default = preg_replace($locale_regex, '/', $file);
            if ($default !== $file) {
                $files = array_flip($files);
                unset($files[$default]);
                $files = array_flip($files);
            }

            // Ignore the default file if we're not on the default locale
            if ($file == $default && $this->localized_cache->getLocale() !== $this->default_locale) {
                $files = array_flip($files);
                unset($files[$file]);
                $files = array_flip($files);
            }
        }

        return array_values($files);
    }
}
