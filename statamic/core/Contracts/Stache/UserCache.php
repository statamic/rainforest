<?php

namespace Statamic\Contracts\Stache;

use Statamic\Contracts\Data\Users\User;
use Statamic\Contracts\Permissions\UserGroup;

interface UserCache
{
    /**
     * @return \Statamic\Data\UserCollection
     */
    public function getUsers();

    /**
     * @param string                        $key
     * @param \Statamic\Contracts\Data\User $user
     * @return mixed
     */
    public function setUser($key, User $user);

    /**
     * Set all the users
     *
     * @param array $users
     */
    public function setUsers($users);

    /**
     * @param string $key
     * @return mixed
     */
    public function removeUser($key);

    /**
     * Get the groups
     *
     * @return \Illuminate\Support\Collection
     */
    public function getGroups();

    /**
     * Get a group
     *
     * @param string $group
     * @return \Statamic\Contracts\Permissions\UserGroup
     */
    public function getGroup($group);

    /**
     * Set a user group
     *
     * @param string                                    $key
     * @param \Statamic\Contracts\Permissions\UserGroup $group
     * @return mixed
     */
    public function setGroup($key, UserGroup $group);

    /**
     * Set all the groups
     *
     * @param array $groups
     */
    public function setGroups($groups);
}
