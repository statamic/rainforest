<?php

namespace Statamic\Addons\Parent;

use Statamic\Extend\Tags;

use Statamic\API\URL;
use Statamic\API\Content;

class ParentTags extends Tags
{
    /**
     * The {{ parent:[field] }} tag
     *
     * Gets a specified field value from the parent.
     *
     * @param  $method
     * @param  $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        $parent = Content::get($this->getParentUrl());

        return array_get($parent, $method);
    }

    /**
     * The {{ parent }} tag
     *
     * On its own, it simply returns the URL of the parent.
     *
     * @return string
     */
    public function index()
    {
        return $this->getParentUrl();
    }

    /**
     * Get the parent URL
     *
     * @return string
     */
    private function getParentUrl()
    {
        return URL::parent(URL::getCurrent());
    }
}
