<?php

namespace Statamic\Addons\Is;

use Statamic\API\User;
use Statamic\API\Roles;
use Statamic\Extend\API;

class IsAPI extends API
{
    public function is($roles)
    {
        // Not logged in? This is the end of the road.
        if (! $user = User::getCurrent()) {
            return;
        }

        $roles = explode('|', $roles);

        foreach ($roles as $slug) {
            // Get the role
            if (! $role = Roles::slug($slug)) {
                // If the role doesn't exist, we'll log an error and move on.
                \Log::error("Role [$slug] doesn't exist");
                continue;
            }

            if ($user->hasRole($role->uuid())) {
                return true;
            }
        }

        return false;
    }
}
