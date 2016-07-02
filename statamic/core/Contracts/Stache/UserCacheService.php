<?php

namespace Statamic\Contracts\Stache;

interface UserCacheService
{
    /**
     * @return \Statamic\Contracts\Stache\UserCache
     */
    public function update();

    /**
     * @return \Statamic\Contracts\Stache\UserCache
     */
    public function load();

    /**
     * @return \Statamic\Data\UserCollection
     */
    public function getAll();

    /**
     * @param string $id
     * @return \Statamic\Data\User
     */
    public function getById($id);

    /**
     * @param string $username
     * @return \Statamic\Data\User
     */
    public function getByUsername($username);

    /**
     * @param string $email
     * @return \Statamic\Data\User
     */
    public function getByEmail($email);

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getAllGroups();

    /**
     * @param string $group
     * @return \Statamic\Contracts\Permissions\UserGroup
     */
    public function getGroup($group);
}
