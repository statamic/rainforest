<?php

namespace Statamic\Extend\Contextual;

use Statamic\Extend\Addon;

class ContextualObject
{
    /**
     * @protected \Statamic\Extend\Addon
     */
    protected $context;

    /**
     * Create a new contextual addon object
     *
     * @param  \Statamic\Extend\Addon  $context
     */
    public function __construct(Addon $context)
    {
        $this->context = $context;
    }

    /**
     * Returns a value prepended by the context
     *
     * @param string $value
     * @return string
     */
    protected function contextualize($value)
    {
        return 'addons:' . $this->context->getAddonClassName() . ':' . $value;
    }
}
