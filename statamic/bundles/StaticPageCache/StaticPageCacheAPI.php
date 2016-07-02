<?php

namespace Statamic\Addons\StaticPageCache;

use Statamic\API\Config;
use Statamic\Extend\API;
use Illuminate\Http\Request;
use Statamic\Addons\StaticPageCache\Cacher\FileCacher;
use Statamic\Addons\StaticPageCache\Cacher\ApplicationCacher;

class StaticPageCacheAPI extends API
{
    /**
     * @var \Statamic\Addons\StaticPageCache\Cacher
     */
    private $cacher;

    /**
     * @return \Statamic\Addons\StaticPageCache\Cacher
     */
    public function cacher()
    {
        if ($this->cacher) {
            return $this->cacher;
        }

        $this->cacher = (Config::get('caching.static_caching_type') === 'file')
            ? new FileCacher($this->cache)
            : new ApplicationCacher($this->cache);

        return $this->cacher;
    }

    public function cachePage(Request $request, $content)
    {
        $this->cacher()->cachePage($request, $content);
    }

    public function getCachedPage(Request $request)
    {
        return $this->cacher()->getCachedPage($request);
    }

    public function clear()
    {
        return $this->cacher()->clear();
    }
}
