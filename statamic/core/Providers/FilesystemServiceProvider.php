<?php

namespace Statamic\Providers;

use Statamic\API\Str;
use Statamic\API\File;
use Statamic\API\YAML;
use Statamic\API\Config;
use Statamic\API\Folder;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Config\Repository;

class FilesystemServiceProvider extends ServiceProvider
{
    public function boot(Repository $config)
    {
        // Start with the default/local disk.
        $this->local = $local = $config->get('filesystems.disks.local');

        $filesystems = Config::get('system.filesystems');

        // Create the content disk. For now, it's just the site/content subdirectory.
        // Eventually it will be configurable by the user to allow them to relocate
        // it, or mount from a cloud service like Dropbox or Amazon S3.
        $content = $this->local;
        $content_root = array_get($filesystems, 'content.root');
        if (Str::startsWith($content_root, '/')) {
            $content['root'] = $content_root;
        } else {
            $content['root'] .= '/' . $content_root;
        }

        // Same with storage
        $storage = $this->local;
        $storage_root = array_get($filesystems, 'storage.root');
        if (Str::startsWith($storage_root, '/')) {
            $storage['root'] = $storage_root;
        } else {
            $storage['root'] .= '/' . $storage_root;
        }

        // Same with users
        $users = $this->local;
        $users_root = array_get($filesystems, 'users.root');
        if (Str::startsWith($users_root, '/')) {
            $users['root'] = $users_root;
        } else {
            $users['root'] .= '/' . $users_root;
        }

        // Same with themes
        $themes = $this->local;
        $themes_root = array_get($filesystems, 'themes.root');
        if (Str::startsWith($themes_root, '/')) {
            $themes['root'] = $themes_root;
        } else {
            $themes['root'] .= '/' . $themes_root;
        }

        // Make another filesystem for the active theme.
        // Exactly the same as the `themes` filesystem, but in the active
        // theme's subdirectory. Just makes things a little easier.
        $theme = $themes;
        $theme['root'] .= '/' . Config::getThemeName();

        // Combine all the disks
        $disks = compact('local', 'content', 'storage', 'users', 'themes', 'theme');

        // Merge into the config. The asset container filesystem bindings
        // rely on the storage disk to be available up-front.
        $config->set('filesystems.disks', $disks);

        // Add disks for each asset container
        foreach ($this->containers() as $id => $container) {
            $disks['assets:'.$id] = $container;
        }

        // Merge them back in again.
        $config->set('filesystems.disks', $disks);
    }

    public function register()
    {
        //
    }

    private function containers()
    {
        $containers = [];

        // Can't use the Asset API here because at this point the asset containers haven't
        // been registered into the Stache, so we'll get them manually.
        $paths = collect_files(Folder::disk('storage')->getFilesRecursively('/'))->filter(function ($path) {
            return Str::endsWith($path, 'container.yaml');
        });

        foreach ($paths as $path) {
            $id = explode('/', $path)[1];

            $yaml = YAML::parse(File::disk('storage')->get($path));

            $driver = array_get($yaml, 'driver', 'local');

            if ($driver === 'local') {
                $config = $this->local;
                $path = $yaml['path'];
                if (Str::startsWith($path, '/')) {
                    // absolute paths get replaced completely
                    $config['root'] = $path;
                } else {
                    // relative paths get appended to the root
                    $config['root'] .= '/'.$path;
                }
            } else {
                $root = array_get($yaml, 'path', '/');
                unset($yaml['path']);
                $config = $yaml;
                $config['root'] = $root;
                $config['visibility'] = 'public';
            }

            $containers[$id] = $config;
        }

        return $containers;
    }
}
