<?php

namespace Statamic\Addons\Cache;

use Statamic\API\URL;
use Statamic\Extend\Tags;

class CacheTags extends Tags
{
    /**
     * @var \Statamic\Addons\Cache\Cache
     */
    private $core;

    public function init()
    {
        $this->core = app('Statamic\Addons\Cache\Cache');
    }

    /**
     * The {{ cache }} tag
     *
     * @return string
     */
    public function index()
    {
        // If disabled, do nothing.
        if (! $this->core->isEnabled()) {
            return $this->parse([]);
        }

        // Create a hash so we can identify it. Include the URL in the hash if this is scoped to the page.
        $hash = ($this->get('scope', 'site') === 'page') ? md5(URL::getCurrent(), $this->content) : md5($this->content);

        $path = 'troves:' . $hash;

        if (! $this->cache->exists($path)) {
            $html = $this->parse([], $this->context);

            $this->cache->put($path, $html);
        }

        return $this->cache->get($path);
    }
}
