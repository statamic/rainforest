<?php

namespace Statamic\Contracts\Data\Globals;

use Statamic\Contracts\Data\Content\Content;

interface GlobalContent extends Content
{
    public function title();
}
