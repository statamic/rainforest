<?php

namespace Statamic\Permissions;

use Statamic\API\Roles;
use Statamic\API\Str;
use Statamic\API\UserGroups;
use Statamic\Contracts\Permissions\Role;
use Statamic\Contracts\Permissions\UserGroup;
use Statamic\API\Permissions as PermissionsAPI;

trait Permissible
{
    /**
     * @var \Illuminate\Support\Collection
     */
    protected $roles = [];

    /**
     * Get the roles for the user
     *
     * @return \Illuminate\Support\Collection
     */
    public function roles()
    {
        if ($this->roles) {
            return $this->roles;
        }

        $roles = $this->get('roles', []);

        return $this->roles = Roles::all()->filter(function($role) use ($roles) {
            return in_array($role->uuid(), $roles);
        });
    }

    /**
     * Does the user have a given role?
     *
     * @param string $role
     * @return bool
     */
    public function hasRole($role)
    {
        $role = ($role instanceof Role) ? $role->uuid() : $role;

        if ($result = $this->roles()->has($role)) {
            return true;
        }

        foreach ($this->groups() as $group) {
            if ($group->hasRole($role)) {
                return true;
            }
        }
    }

    /**
     * Does the user have a given permission?
     *
     * @param string $permission
     * @return bool
     */
    public function hasPermission($permission)
    {
        if ($this->get('super') === true) {
            return true;
        }

        foreach ($this->roles() as $role) {
            if ($role->hasPermission($permission)) {
                return true;
            }
        }

        foreach ($this->groups() as $group) {
            if ($group->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all the user's permissions
     *
     * @return mixed
     */
    public function permissions()
    {
        $permissions = [];

        if ($this->isSuper()) {
            return PermissionsAPI::all();
        }

        foreach ($this->roles() as $role) {
            $permissions = array_merge($permissions, $role->permissions()->all());
        }

        return $permissions;
    }

    /**
     * Is this a super user?
     *
     * @return bool
     */
    public function isSuper()
    {
        return $this->hasPermission('super');
    }

    /**
     * Get the user's groups
     *
     * @param array|null $groups
     * @return \Illuminate\Support\Collection
     */
    public function groups($groups = null)
    {
        if (is_null($groups)) {
            return UserGroups::forUser($this);
        }

        // Go through all the groups, add the user to any group specified, and remove from the others.
        foreach (UserGroups::all() as $group_uuid => $group) {
            if (in_array($group_uuid, $groups)) {
                $group->addUser($this);
            } else {
                $group->removeUser($this);
            }

            $group->save();
        }
    }

    /**
     * Does this user belong to a given group?
     *
     * @param string $group
     * @return bool
     */
    public function inGroup($group)
    {
        $group = ($group instanceof UserGroup) ? $group->uuid() : $group;

        return $this->groups()->has($group);
    }
}
