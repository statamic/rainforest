<?php

namespace Statamic\Addons\StaticPageCache\Cacher;

use Statamic\API\Config;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Statamic\Extend\Contextual\ContextualCache;

abstract class Cacher
{
    /**
     * @var \Statamic\Extend\Contextual\ContextualCache
     */
    protected $cache;

    /**
     * @param \Statamic\Extend\Contextual\ContextualCache $cache
     */
    public function __construct(ContextualCache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param \Illuminate\Http\Request $request     Request associated with the page to be cached
     * @param string                   $content     The response content to be cached
     * @param null|int                 $expiration  Length of time to cache for, in minutes
     */
    abstract public function cachePage(Request $request, $content, $expiration = null);

    /**
     * @param \Illuminate\Http\Request $request
     * @return string
     */
    abstract public function getCachedPage(Request $request);

    /**
     * @return void
     */
    abstract public function clear();

    /**
     * @return int
     */
    protected function getDefaultExpiration()
    {
        return Config::get('caching.static_caching_default_cache_length');
    }

    /**
     * @param  mixed $content
     * @return string
     */
    protected function normalizeContent($content)
    {
        if ($content instanceof Response) {
            $content = $content->content();
        }

        return $content;
    }
}
