<?php

namespace Statamic\Addons\In;

use Statamic\API\User;
use Statamic\Extend\API;
use Statamic\API\UserGroups;

class InAPI extends API
{
    public function in($groups)
    {
        // Not logged in? This is the end of the road.
        if (! $user = User::getCurrent()) {
            return;
        }

        $groups = explode('|', $groups);

        foreach ($groups as $slug) {
            // Get the group
            if (! $group = UserGroups::slug($slug)) {
                // If the group doesn't exist, we'll log an error and move on.
                \Log::error("Group [$slug] doesn't exist");
                continue;
            }

            if ($user->inGroup($group->uuid())) {
                return true;
            }
        }

        return false;
    }
}
