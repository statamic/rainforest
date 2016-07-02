<?php

namespace Statamic\Extend;

/**
 * The addon itself. All addon aspects extend this.
 */
class Addon
{
    use Extensible;

    /**
     * Create a new Addon instance
     *
     * @param string|null $name  Name of the addon
     */
    public function __construct($name = null)
    {
        $this->bootstrap();
        $this->buildAddon($name);
        $this->init();
    }
}
