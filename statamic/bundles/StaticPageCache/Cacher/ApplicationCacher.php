<?php

namespace Statamic\Addons\StaticPageCache\Cacher;

use Statamic\API\Config;
use Statamic\API\Folder;
use Illuminate\Http\Request;

class ApplicationCacher extends Cacher
{
    /**
     * @param \Illuminate\Http\Request $request     Request associated with the page to be cached
     * @param string                   $content     The response content to be cached
     * @param null|int                 $expiration  Length of time to cache for, in minutes
     */
    public function cachePage(Request $request, $content, $expiration = null)
    {
        $expiration = $expiration ?: $this->getDefaultExpiration();

        $content = $this->normalizeContent($content);

        $this->cache->put($this->makeHash($request), $content, $expiration);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return string
     */
    public function getCachedPage(Request $request)
    {
        return $this->cache->get($this->makeHash($request));
    }

    /**
     * @return void
     */
    public function clear()
    {
        // This is not the right way to do this. Right now it assumes the Laravel
        // caching driver being used is "File". (In most cases it will be)
        // Todo: Make this not dependendent on the Laravel file cache driver
        Folder::delete(storage_path('framework/cache/addons/StaticPagecache'));
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return string
     */
    protected function makeHash(Request $request)
    {
        $path = $request->path();

        if (! Config::get('static_caching_ignore_query_strings')) {
            $path .= '?' . http_build_query($request->query->all());
        }

        return md5($path);
    }
}
