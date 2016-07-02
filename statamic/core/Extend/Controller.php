<?php

namespace Statamic\Extend;

use Statamic\API\Path;
use Statamic\Http\Controllers\CpController;

class Controller extends CpController
{
    use Extensible;

    public function __construct()
    {
        parent::__construct(app('request'));

        $name = explode('\\', get_called_class())[2];
        $this->buildAddon($name);
        $this->init();
    }
}
