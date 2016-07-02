<?php

namespace Statamic\Providers;

use Illuminate\Auth\AuthManager;
use Statamic\Permissions\Permissions;
use Statamic\Extensions\FileUserProvider;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [];

    public function register()
    {
        $this->app->singleton('permissions', function () {
            return new Permissions;
        });
        $this->app->alias('permissions', 'Statamic\Permissions\Permissions');
    }

    public function boot(AuthManager $auth, GateContract $gate, Permissions $permissions)
    {
        parent::registerPolicies($gate);

        $permissions->build();

        foreach ($permissions->all(true) as $group => $permission) {
            $gate->define($permission, function ($user) use ($permission) {
                return $user->isSuper() || $user->hasPermission($permission);
            });
        }

        $auth->extend('file', function($app) {
            return new FileUserProvider();
        });
    }
}
