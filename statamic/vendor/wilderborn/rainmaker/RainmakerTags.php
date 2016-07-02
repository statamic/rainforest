<?php

namespace Statamic\Addons\Rainmaker;

use Statamic\Extend\Tags;

class RainmakerTags extends Tags
{
    public function checkoutAction()
    {
        return $this->eventUrl('process');
    }
}
