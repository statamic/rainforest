<?php

namespace Statamic\API;

use Statamic\Contracts\Permissions\Permissible;

class UserGroups
{
    /**
     * @return \Statamic\Contracts\Permissions\UserGroupService
     */
    private static function groups()
    {
        return app('Statamic\Contracts\Permissions\UserGroupService');
    }

    /**
     * Get all the groups
     */
    public static function all()
    {
        return self::groups()->getAll();
    }

    /**
     * Get a user group by name
     *
     * @param string $group
     * @return \Statamic\Contracts\Permissions\UserGroup
     */
    public static function get($group)
    {
        return self::groups()->get($group);
    }

    /**
     * Get a group by slug
     *
     * @param  string $slug
     * @return \Statamic\Contracts\Permissions\UserGroup
     */
    public static function slug($slug)
    {
        return self::groups()->slug($slug);
    }

    /**
     * Get the user groups for a given user
     *
     * @param string|\Statamic\Contracts\Permissions\Permissible $user
     * @return \Illuminate\Support\Collection
     */
    public static function forUser($user)
    {
        $groups = [];

        // If a User object was provided, we'll just get the UUID
        $user = ($user instanceof Permissible) ? $user->id() : $user;

        foreach (self::all() as $group_uuid => $group) {
            if ($group->hasUser($user)) {
                $groups[$group_uuid] = $group;
            }
        }

        return collect($groups);
    }

}
