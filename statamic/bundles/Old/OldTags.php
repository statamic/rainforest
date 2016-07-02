<?php

namespace Statamic\Addons\Old;

use Statamic\Extend\Tags;

class OldTags extends Tags
{
    public function __call($method, $args)
    {
        $var = explode(':', $this->tag)[1];

        return old($var);
    }
}
