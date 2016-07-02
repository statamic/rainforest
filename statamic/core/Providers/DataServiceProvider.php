<?php

namespace Statamic\Providers;

use Illuminate\Support\ServiceProvider;
use Statamic\Data\Content\File\ContentService;
use Statamic\Data\Content\File\OrderParser;
use Statamic\Data\Content\File\PathBuilder;
use Statamic\Data\Content\File\StatusParser;
use Statamic\Data\Content\UrlBuilder;
use Statamic\Data\Entries\File\CollectionFolder;
use Statamic\Data\Entries\File\EntryFactory;
use Statamic\Data\Globals\File\GlobalFactory;
use Statamic\Data\Pages\File\PageFactory;
use Statamic\Data\Pages\File\PageFolder;
use Statamic\Data\Pages\File\PageTreeReorderer;
use Statamic\Data\Taxonomies\File\TermFactory;
use Statamic\Data\Taxonomies\File\Taxonomy;

class DataServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->bindContent();
        $this->bindPages();
        $this->bindEntries();
        $this->bindTaxonomies();
        $this->bindGlobals();
    }

    public function bindContent()
    {
        $this->app->singleton('Statamic\Contracts\Data\Content\ContentService', function() {
            return new ContentService(app('Statamic\Contracts\Stache\ContentCacheService'));
        });

        $this->app->bind('Statamic\Contracts\Data\Content\OrderParser', function() {
            return new OrderParser;
        });

        $this->app->bind('Statamic\Contracts\Data\Content\StatusParser', function() {
            return new StatusParser;
        });

        $this->app->bind('Statamic\Contracts\Data\Content\PathBuilder', function() {
            return new PathBuilder;
        });

        $this->app->bind('Statamic\Contracts\Data\Content\UrlBuilder', function() {
            return new UrlBuilder;
        });
    }

    public function bindPages()
    {
        $this->app->bind('Statamic\Contracts\Data\Pages\PageFactory', function() {
            return new PageFactory;
        });

        $this->app->bind('Statamic\Contracts\Data\Pages\PageFolder', function() {
            return new PageFolder;
        });

        $this->app->bind('Statamic\Contracts\Data\Pages\PageTreeReorderer', function() {
            return new PageTreeReorderer;
        });
    }

    public function bindEntries()
    {
        $this->app->bind('Statamic\Contracts\Data\Entries\EntryFactory', function() {
            return new EntryFactory;
        });

        $this->app->bind('Statamic\Contracts\Data\Entries\CollectionFolder', function() {
            return new CollectionFolder;
        });
    }

    public function bindTaxonomies()
    {
        $this->app->bind('Statamic\Contracts\Data\Taxonomies\TermFactory', function() {
            return new TermFactory;
        });

        $this->app->bind('Statamic\Contracts\Data\Taxonomies\Taxonomy', function() {
            return new Taxonomy;
        });
    }

    public function bindGlobals()
    {
        $this->app->bind('Statamic\Contracts\Data\Globals\GlobalFactory', function() {
            return new GlobalFactory;
        });
    }
}
