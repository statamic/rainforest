<?php

namespace Statamic\Providers;

use Illuminate\Support\ServiceProvider;

class UserServiceProvider extends ServiceProvider
{
    private $user_driver;

    public function register()
    {
        $this->user_driver = env('USER_SERVICE_DRIVER', 'File');

        $this->app->bind('Statamic\Contracts\Data\Users\User', function() {
            return app("Statamic\\Data\\Users\\{$this->user_driver}\\User");
        });

        $this->app->singleton('Statamic\Contracts\Data\Users\UserService', function() {
            $user_cacher = app('Statamic\Contracts\Stache\UserCacheService');

            $class = "Statamic\\Data\\Users\\{$this->user_driver}\\UserService";

            return new $class($user_cacher);
        });

        $this->app->bind('Statamic\Contracts\Data\Users\UserFactory', function() {
            $class = "Statamic\\Data\\Users\\{$this->user_driver}\\UserFactory";

            return new $class;
        });

        $this->app->bind('Statamic\Contracts\Permissions\Role', function() {
            return app("Statamic\\Permissions\\{$this->user_driver}\\Role");
        });

        $this->app->singleton('Statamic\Contracts\Permissions\RoleFactory', function() {
            return app("Statamic\\Permissions\\{$this->user_driver}\\RoleFactory");
        });

        $this->app->bind('Statamic\Contracts\Permissions\UserGroup', function() {
            return app("Statamic\\Permissions\\{$this->user_driver}\\UserGroup");
        });

        $this->app->singleton('Statamic\Contracts\Permissions\UserGroupFactory', function() {
            return app("Statamic\\Permissions\\{$this->user_driver}\\UserGroupFactory");
        });

        $this->app->singleton('Statamic\Contracts\Permissions\UserGroupService', function() {
            $user_cacher = app('Statamic\Contracts\Stache\UserCacheService');

            $class = "Statamic\\Permissions\\{$this->user_driver}\\UserGroupService";

            return new $class($user_cacher);
        });
    }
}
