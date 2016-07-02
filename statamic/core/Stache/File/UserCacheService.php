<?php

namespace Statamic\Stache\File;

use Statamic\Contracts\Stache\UserCache as UserCacheContract;
use Statamic\Contracts\Stache\UserCacheService as UserCacheServiceContract;
use Statamic\Contracts\Stache\UserCacheUpdater as UserCacheUpdaterContract;

class UserCacheService implements UserCacheServiceContract
{
    /**
     * @var \Statamic\Contracts\Stache\UserCache
     */
    private $cache;

    /**
     * @var \Statamic\Contracts\Stache\UserCacheUpdater
     */
    private $updater;

    /**
     * @param \Statamic\Contracts\Stache\UserCache        $cache
     * @param \Statamic\Contracts\Stache\UserCacheUpdater $updater
     */
    public function __construct(UserCacheContract $cache, UserCacheUpdaterContract $updater)
    {
        $this->cache = $cache;
        $this->updater = $updater;
    }

    /**
     * @return \Statamic\Contracts\Stache\UserCache
     */
    public function load()
    {
        return $this->updater->load($this->cache);
    }

    /**
     * @return \Statamic\Contracts\Stache\UserCache
     */
    public function update()
    {
        return $this->updater->update($this->cache);
    }

    /**
     * @return \Statamic\Data\UserCollection
     */
    public function getAll()
    {
        return $this->cache->getUsers();
    }

    /**
     * @param string $id
     * @return \Statamic\Contracts\Data\User
     */
    public function getById($id)
    {
        return $this->cache->getUsers()->filter(function($user) use ($id) {
            return $user->id() == $id;
        })->first();
    }

    /**
     * @param string $username
     * @return \Statamic\Contracts\Data\User
     */
    public function getByUsername($username)
    {
        return $this->cache->getUsers()->filter(function($user) use ($username) {
            return $user->username() == $username;
        })->first();
    }

    /**
     * @param string $email
     * @return \Statamic\Contracts\Data\User
     */
    public function getByEmail($email)
    {
        return $this->cache->getUsers()->filter(function($user) use ($email) {
            return $user->email() == $email;
        })->first();
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getAllGroups()
    {
        return $this->cache->getGroups();
    }

    /**
     * @param string $group
     * @return \Statamic\Contracts\Permissions\UserGroup
     */
    public function getGroup($group)
    {
        return $this->cache->getGroup($group);
    }
}
