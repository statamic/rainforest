<?php

namespace Statamic\Addons\Entries;

use Statamic\API\URL;
use Statamic\API\Str;
use Statamic\API\Content;
use Statamic\Addons\Collection\CollectionTags;

class EntriesTags extends CollectionTags
{
    /**
     * Catch-all for any child tags
     * 
     * @param string $method
     * @param array  $args
     * @return string
     **/
    public function __call($method, $arguments)
    {
        return $this->index();
    }

    /**
     * Maps to `{{ entries }}`
     *
     * @return string
     */
    public function index()
    {
        $from = $this->get(['from', 'folder', 'url'], URL::getCurrent());

        $from = Str::ensureLeft($from, '/');

        $this->collection = Content::pageRaw($from)->entries();

        $this->filter();

        if ($this->collection->isEmpty()) {
            return $this->parse(['no_results' => true]);
        }

        return $this->output();
    }
}
