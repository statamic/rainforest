<?php

namespace Statamic\Stache\File;

use Statamic\API\File;
use Statamic\API\Path;
use Statamic\API\User;
use Statamic\API\YAML;
use Statamic\API\Cache;
use Statamic\API\Folder;
use Statamic\Contracts\Stache\UserCache as UserCacheContract;
use Statamic\Contracts\Stache\UserCacheUpdater as UserCacheUpdaterContract;

class UserCacheUpdater implements UserCacheUpdaterContract
{
    /**
     * @var \League\Flysystem\Filesystem
     */
    private $filesystem;

    /**
     * @var \Statamic\Stache\File\UserCache
     */
    private $cache;

    /**
     * @var \Illuminate\Support\Collection
     */
    private $timestamps;

    /**
     * @var array
     */
    private $cached_timestamps;

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
     * @var string
     */
    private $usergroup_path;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->usergroup_path = Path::makeRelative(settings_path('users/groups.yaml'));
    }

    /**
     * Load the cache
     *
     * @param \Statamic\Contracts\Stache\UserCache $cache
     * @return \Statamic\Contracts\Stache\UserCache
     */
    public function load(UserCacheContract $cache)
    {
        $this->cache = $cache;

        return $this->buildCacheFromFile();
    }

    /**
     * Update the cache
     *
     * @param \Statamic\Contracts\Stache\UserCache $cache
     * @return \Statamic\Contracts\Stache\UserCache
     */
    public function update(UserCacheContract $cache)
    {
        $this->filesystem = app('filesystem')->disk('users')->getDriver();

        $this->cache = $cache;

        // Grab all files so we have a basis for comparisons
        $all_files = $this->getAllFiles();
        $this->timestamps = $all_files->lists('timestamp', 'path');
        $this->files = $all_files->lists('path');

        // Add the user group file
        if (File::exists($this->usergroup_path)) {
            $this->timestamps->put($this->usergroup_path, File::lastModified($this->usergroup_path));
            $this->files->push($this->usergroup_path);
        }

        // Populate the cache with some essentials
        $this->cache->setFiles($this->files);
        $this->cache->setTimestamps($this->getTimestampsFromFile());

        // Get deleted and modified files
        $this->deleted_files = $this->getDeletedFiles();
        $this->modified_files = $this->getModifiedFiles();

        // Load the cache from disk
        $this->cache = $this->load($this->cache);

        // No modifications? We're done. Just return the cache.
        if ($this->modified_files->isEmpty() && $this->deleted_files->isEmpty()) {
            return $this->cache;
        }

        $this->cache->hasBeenUpdated(true);

        $this->removeDeletedUsers();
        $this->updateUsers();

        $this->writeCache();

        return $this->cache;
    }

    /**
     * Get all the user files
     *
     * @return \Illuminate\Support\Collection
     */
    private function getAllFiles()
    {
        $path = '/';

        $files = collect($this->filesystem->listContents($path))->filter(function($file) {
            return $file['extension'] === 'yaml';
        });

        return $files;
    }

    /**
     * Get all the timestamps from file
     *
     * @return array
     */
    private function getTimestampsFromFile()
    {
        if ($this->cached_timestamps) {
            return $this->cached_timestamps;
        }

        if ($timestamps = Cache::get('stache/users/timestamps')) {
            return $this->cached_timestamps = unserialize($timestamps);
        }

        return [];
    }

    /**
     * Get all the modified files
     *
     * @return \Illuminate\Support\Collection
     */
    private function getModifiedFiles()
    {
        $modified_files = [];

        // If there's no cache, then we already know whats modified. Everything.
        if (! $this->cached_timestamps) {
            return $this->files;
        }

        // Get all the paths of files that have been modified
        foreach ($this->timestamps as $file => $timestamp) {
            if (isset($this->getTimestampsFromFile()[$file])
                && $timestamp > $this->getTimestampsFromFile()[$file]
            ) {
                $modified_files[] = $file;
            };
        }

        // Get new files
        $new_files = array_diff(
            $this->files->all(),
            array_keys($this->getTimestampsFromFile())
        );

        return collect(array_merge($modified_files, $new_files));
    }

    /**
     * Get all the deleted files
     *
     * @return \Illuminate\Support\Collection
     */
    private function getDeletedFiles()
    {
        return collect(array_diff(
            array_keys($this->getTimestampsFromFile()),
            $this->files->all()
        ));
    }


    /**
     * Build up a cache object from file
     *
     * @return \Statamic\Contracts\Stache\UserCache
     */
    private function buildCacheFromFile()
    {
        if ($users = Cache::get('stache/users/users')) {
            $this->cache->setUsers(unserialize($users));
        }

        if ($groups = Cache::get('stache/users/groups')) {
            $this->cache->setGroups(unserialize($groups));
        }

        return $this->cache;
    }

    /**
     * Remove deleted users
     */
    private function removeDeletedUsers()
    {
        foreach ($this->deleted_files as $path) {
            $this->cache->removeTimestamp($path);
            $this->cache->removeUser($path);
        }
    }

    /**
     * Update modified users
     */
    private function updateUsers()
    {
        foreach ($this->modified_files as $filename) {
            if ($filename === $this->usergroup_path) {
                $this->updateUserGroups();

            } else {
                $yaml = YAML::parse(File::disk('users')->get($filename));

                $user = User::create()
                    ->with($yaml)
                    ->username(pathinfo($filename)['filename'])
                    ->get();

                // If they don't already have a UUID (for example, if the file was created
                // manually) we should go ahead and generate one, and re-save the file.
                $user->ensureId(true);

                // Ensure that the password is hashed if it's currently only plain text.
                $user->ensureSecured();

                $this->cache->setUser($filename, $user);
            }

            $timestamp = ($filename === $this->usergroup_path)
                ? File::lastModified($filename)
                : File::disk('users')->lastModified($filename);

            $this->cache->setTimestamp($filename, $timestamp);
        }
    }

    /**
     * Update user groups
     */
    private function updateUserGroups()
    {
        $yaml = YAML::parse(File::get($this->usergroup_path));

        $this->cache->setGroups([]);

        foreach ($yaml as $group_uuid => $group_data) {
            $group = app('Statamic\Contracts\Permissions\UserGroupFactory')->create($group_data, $group_uuid);

            $this->cache->setGroup($group_uuid, $group);
        }
    }

    /**
     * Write the cache to file
     */
    private function writeCache()
    {
        Cache::put('stache/users/timestamps', serialize($this->cache->getTimestamps()));

        Cache::put('stache/users/users', serialize($this->cache->getUsers()));

        Cache::put('stache/users/groups', serialize($this->cache->getGroups()));
    }
}
