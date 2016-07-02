<?php

namespace Statamic\Providers;

use Statamic\CP\Fieldset;
use Illuminate\Support\ServiceProvider;

class CpServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('Statamic\Contracts\CP\Fieldset', function() {
            return new Fieldset;
        });
    }
}
