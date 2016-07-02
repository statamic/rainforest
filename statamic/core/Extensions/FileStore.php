<?php

namespace Statamic\Extensions;

use Illuminate\Contracts\Cache\Store;
use Illuminate\Cache\FileStore as LaravelFileStore;

class FileStore extends LaravelFileStore implements Store
{
    /**
     * Get the full path for the given cache key.
     *
     * @param  string  $key
     * @return string
     */
    protected function path($key)
    {
        $namespaces = explode(':', $key);
        array_pop($namespaces);

        $parts = array_slice(str_split($hash = md5($key), 2), 0, 2);

        return $this->directory.'/'.implode('/', $namespaces).'/'.implode('/', $parts).'/'.$hash;
    }
}
