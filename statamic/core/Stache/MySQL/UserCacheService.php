<?php

namespace Statamic\Stache\MySQL;

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
     * @return \Statamic\Data\User
     */
    public function getById($id)
    {
        return $this->cache->getById($id);
    }

    /**
     * @param string $username
     * @return \Statamic\Data\User
     */
    public function getByUsername($username)
    {
        return $this->cache->getByUsername($username);
    }

    /**
     * @param string $email
     * @return \Statamic\Data\User
     */
    public function getByEmail($email)
    {
        return $this->cache->getByEmail($email);
    }
}
