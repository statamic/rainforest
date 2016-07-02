<?php

namespace Statamic\Addons\Redirect;

use Statamic\Extend\Tags;
use Statamic\Exceptions\RedirectException;

class RedirectTags extends Tags
{
    public function index()
    {
        $e = new RedirectException;

        $e->setUrl($this->get(['to', 'url']));
        $e->setStatusCode($this->get('response', 302));

        throw $e;
    }
}
