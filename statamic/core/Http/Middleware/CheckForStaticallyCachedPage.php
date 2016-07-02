<?php

namespace Statamic\Http\Middleware;

use Closure;
use Statamic\API\Config;

class CheckForStaticallyCachedPage
{
    /**
     * @var \Statamic\Addons\StaticPageCache\StaticPageCacheAPI
     */
    private $cacher;

    public function __construct()
    {
        $this->cacher = addon('StaticPageCache');
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Config::get('caching.static_caching_enabled')) {
            if ($cached = $this->cacher->getCachedPage($request)) {
                return response($cached);
            }
        }

        return $next($request);
    }
}
