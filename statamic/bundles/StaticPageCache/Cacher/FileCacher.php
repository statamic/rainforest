<?php

namespace Statamic\Addons\StaticPageCache\Cacher;

use Statamic\API\File;
use Statamic\API\Folder;
use Illuminate\Http\Request;

class FileCacher extends Cacher
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

        $path = '/static/' . $request->path() . '/index.html';

        File::put($path, $content);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return string
     */
    public function getCachedPage(Request $request)
    {
        // This method doesn't get used when using file-based static caching.
        // The html file will get served before PHP even gets a chance.
    }

    /**
     * @return void
     */
    public function clear()
    {
        foreach (Folder::getFilesRecursively('static') as $path) {
            File::delete($path);
        }
    }
}
