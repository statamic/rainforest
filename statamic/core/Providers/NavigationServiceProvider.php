<?php

namespace Statamic\Providers;

use Statamic\CP\Navigation\Nav;
use Illuminate\Support\ServiceProvider;

class NavigationServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('Statamic\CP\Navigation\Nav', function () {
            return new Nav;
        });
    }
}
