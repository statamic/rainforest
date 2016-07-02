<?php

namespace Statamic\Providers;

use Illuminate\Http\Request;
use Statamic\API\Config;
use Statamic\API\Stache;
use Statamic\API\Str;
use Statamic\Stache\Cache;
use Statamic\Stache\CacheService;
use Statamic\API\Cache as CacheAPI;
use Statamic\Stache\File\ContentCache;
use Illuminate\Support\ServiceProvider;
use Statamic\Stache\File\ContentCacheService;
use Statamic\Stache\File\ContentCacheUpdater;
use Statamic\Stache\File\LocalizedContentCacheService;
use Statamic\Stache\File\LocalizedContentCacheUpdater;

class CacheServiceProvider extends ServiceProvider
{
    private $user_driver;

    private $asset_driver;

    private $request;

    public function register()
    {
        $this->user_driver = env('USER_CACHE_DRIVER', 'File');
        $this->asset_driver = env('ASSET_CACHE_DRIVER', 'File');

        $this->registerUserCaches();
        $this->registerAssetCaches();
        $this->registerContentCaches();

        $this->app->singleton('Statamic\Contracts\Stache\Cache', function() {
            return new Cache;
        });

        $this->app->singleton('Statamic\Contracts\Stache\CacheService', function() {
            return new CacheService(
                app('Statamic\Contracts\Stache\Cache'),
                app('Statamic\Contracts\Stache\ContentCacheService'),
                app('Statamic\Contracts\Stache\UserCacheService'),
                app('Statamic\Contracts\Stache\AssetCacheService')
            );
        });
    }

    private function registerContentCaches()
    {
        $this->app->singleton('Statamic\Contracts\Stache\ContentCache', function() {
            return app('Statamic\Contracts\Stache\Cache')->getContent() ?: new ContentCache();
        });

        $this->app->singleton('Statamic\Contracts\Stache\ContentCacheUpdater', function() {
            return new ContentCacheUpdater;
        });

        $this->app->singleton('Statamic\Contracts\Stache\ContentCacheService', function() {
            return new ContentCacheService(
                app('Statamic\Contracts\Stache\ContentCache'),
                app('Statamic\Contracts\Stache\ContentCacheUpdater')
            );
        });

        $this->app->bind(
            'Statamic\Contracts\Stache\LocalizedContentCache',
            'Statamic\Stache\File\LocalizedContentCache'
        );

        $this->app->singleton('Statamic\Contracts\Stache\LocalizedContentCacheUpdater', function() {
            return new LocalizedContentCacheUpdater(
                app('Statamic\Contracts\Stache\ContentCache')
            );
        });

        $this->app->singleton('Statamic\Contracts\Stache\LocalizedContentCacheService', function() {
            return new LocalizedContentCacheService(
                app('Statamic\Contracts\Stache\LocalizedContentCacheUpdater')
            );
        });
    }

    private function registerAssetCaches()
    {
        $this->app->singleton('Statamic\Contracts\Stache\AssetCache', function() {
            if ($assets = app('Statamic\Contracts\Stache\Cache')->getAssets()) {
                return $assets;
            }

            $class = "Statamic\\Stache\\{$this->asset_driver}\\AssetCache";

            return new $class();
        });

        $this->app->singleton('Statamic\Contracts\Stache\AssetCacheUpdater', function() {
            $class = "Statamic\\Stache\\{$this->asset_driver}\\AssetCacheUpdater";

            return new $class();
        });

        $this->app->singleton('Statamic\Contracts\Stache\AssetCacheService', function() {
            $class = "Statamic\\Stache\\{$this->asset_driver}\\AssetCacheService";

            return new $class(
                app('Statamic\Contracts\Stache\AssetCache'),
                app('Statamic\Contracts\Stache\AssetCacheUpdater')
            );
        });

        $this->app->bind(
            'Statamic\Contracts\Stache\LocalizedAssetCache',
            "Statamic\\Stache\\{$this->asset_driver}\\LocalizedAssetCache"
        );

        $this->app->singleton('Statamic\Contracts\Stache\LocalizedAssetCacheUpdater', function() {
            $class = "Statamic\\Stache\\{$this->asset_driver}\\LocalizedAssetCacheUpdater";

            return new $class(
                app('Statamic\Contracts\Stache\AssetCache')
            );
        });

        $this->app->singleton('Statamic\Contracts\Stache\LocalizedAssetCacheService', function() {
            $class = "Statamic\\Stache\\{$this->asset_driver}\\LocalizedAssetCacheService";

            return new $class(
                app('Statamic\Contracts\Stache\LocalizedAssetCacheUpdater')
            );
        });
    }

    private function registerUserCaches()
    {
        $this->app->singleton('Statamic\Contracts\Stache\UserCache', function() {
            if ($users = app('Statamic\Contracts\Stache\Cache')->getUsers()) {
                return $users;
            }

            $class = "Statamic\\Stache\\{$this->user_driver}\\UserCache";

            return new $class();
        });

        $this->app->singleton('Statamic\Contracts\Stache\UserCacheUpdater', function() {
            $class = "Statamic\\Stache\\{$this->asset_driver}\\UserCacheUpdater";

            return new $class();
        });

        $this->app->singleton('Statamic\Contracts\Stache\UserCacheService', function() {
            $class = "Statamic\\Stache\\{$this->asset_driver}\\UserCacheService";

            return new $class(
                app('Statamic\Contracts\Stache\UserCache'),
                app('Statamic\Contracts\Stache\UserCacheUpdater')
            );
        });
    }

    /**
     * Load the Stache
     *
     * @param \Illuminate\Http\Request $request
     */
    public function boot(Request $request)
    {
        $this->request = $request;

        if ($this->shouldUpdate()) {
            Stache::update();
        } else {
            Stache::load();
        }
    }

    /**
     * Should the Stache get updated rather than just loaded?
     *
     * @return bool
     */
    private function shouldUpdate()
    {
        // There's no Stache built? We'll need one, that's for sure.
        if (! Stache::exists()) {
            return true;
        }

        // Always-updating settings is off? Short-circuit here. Don't update.
        if (! Config::get('caching.stache_always_update')) {
            return false;
        }

        // Is this a Glide route? We don't want to update for those.
        $glide_route = ltrim(Str::ensureRight(Config::get('assets.image_manipulation_route'), '/'), '/');
        if (Str::startsWith($this->request->path(), $glide_route)) {
            return false;
        }

        // Got this far? We'll update.
        return true;
    }
}
