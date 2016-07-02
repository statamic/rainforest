<?php

namespace Statamic\Addons\Users;

use Statamic\API\User;
use Statamic\API\Roles;
use Statamic\API\UserGroups;
use Statamic\Addons\Collection\CollectionTags;

class UsersTags extends CollectionTags
{
    public function index()
    {
        $this->collection = collect_content(User::all());

        if ($group = $this->get('group')) {
            $this->filterByGroup($group);
        }

        if ($role = $this->get('role')) {
            $this->filterByRole($role);
        }

        $this->filter();

        return $this->output();
    }

    public function getSortOrder()
    {
        return $this->get('sort', 'username');
    }

    protected function filterByGroup($group)
    {
        $group = UserGroups::slug($group);

        $this->collection = $this->collection->filter(function ($user) use ($group) {
            return $user->inGroup($group);
        });
    }

    protected function filterByRole($role)
    {
        $role = Roles::slug($role);

        $this->collection = $this->collection->filter(function ($user) use ($role) {
            return $user->hasRole($role);
        });
    }
}
