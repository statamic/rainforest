<?php

namespace Statamic\Extend;

use Illuminate\Console\Command as LaravelCommand;

class Command extends LaravelCommand
{
    use Extensible;

    public function __construct()
    {
        parent::__construct();

        $name = explode('\\', get_called_class())[2];
        $this->buildAddon($name);
    }
}
