<?php

namespace Statamic\API;

class Roles
{
    /**
     * Retrieve the roles from storage
     *
     * @return \Illuminate\Support\Collection
     */
    private static function roles()
    {
        return collect(datastore()->getScope('roles', []));
    }

    /**
     * Get all the roles
     */
    public static function all()
    {
        return self::roles()->sortBy('title');
    }

    /**
     * Get a role
     *
     * @param string $id
     * @return \Statamic\Contracts\Permissions\Role
     */
    public static function get($id)
    {
        return self::roles()->get($id);
    }

    /**
     * Get a role by slug
     *
     * @param  string $slug
     * @return \Statamic\Contracts\Permissions\Role
     */
    public static function slug($slug)
    {
        return self::roles()->filter(function ($role) use ($slug) {
            return $role->slug() === $slug;
        })->first();
    }
}
