<?php

namespace Statamic\Providers;

use Statamic\Search\Search;
use Illuminate\Support\ServiceProvider;

class SearchServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('Statamic\Contracts\Search\Search', function ($app) {
            return new Search(new \Mmanos\Search\Search);
        });
    }
}
