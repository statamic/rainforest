<?php

namespace Statamic\Stache\File;

use Statamic\Contracts\Data\Users\User;
use Illuminate\Support\Collection;
use Statamic\Contracts\Permissions\UserGroup;
use Statamic\Contracts\Stache\UserCache as UserCacheContract;

class UserCache implements UserCacheContract
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
     * @var \Statamic\Data\UserCollection
     */
    private $users;

    /**
     * @var \Illuminate\Support\Collection
     */
    private $groups;

    /**
     * Whether an update has occurred
     *
     * @var bool
     */
    private $updated = false;

    /**
     * Create a new UserCache instance
     */
    public function __construct()
    {
        $this->users = collect_users();
        $this->groups = collect();
    }

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
     * Get all timestamps
     *
     * @return array
     */
    public function getTimestamps()
    {
        return $this->timestamps;
    }

    /**
     * Set all timestamps
     *
     * @param array $timestamps
     */
    public function setTimestamps($timestamps)
    {
        $this->timestamps = $timestamps;
    }

    /**
     * Set a single timestamp
     *
     * @param string $path
     * @param int    $timestamp
     */
    public function setTimestamp($path, $timestamp)
    {
        $this->timestamps[$path] = $timestamp;
    }

    /**
     * Remove a single timestamp
     *
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
     * @return \Statamic\Data\UserCollection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param string                        $key
     * @param \Statamic\Contracts\Data\User $user
     * @return mixed
     */
    public function setUser($key, User $user)
    {
        $this->users->put($key, $user);
    }

    /**
     * @param array $users
     */
    public function setUsers($users)
    {
        $this->users = $users;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function removeUser($key)
    {
        $this->users->forget($key);
    }

    /**
     * Get the groups
     *
     * @return \Illuminate\Support\Collection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Get a group
     *
     * @param string $group
     * @return \Statamic\Contracts\Permissions\UserGroup
     */
    public function getGroup($group)
    {
        return $this->groups->get($group);
    }

    /**
     * Set a user group
     *
     * @param string                                    $key
     * @param \Statamic\Contracts\Permissions\UserGroup $group
     * @return mixed
     */
    public function setGroup($key, UserGroup $group)
    {
        $this->groups->put($key, $group);
    }

    /**
     * Set all the groups
     *
     * @param array $groups
     */
    public function setGroups($groups)
    {
        $this->groups = new Collection($groups);
    }
}
