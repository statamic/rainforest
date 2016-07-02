<?php

namespace Statamic\Extend;

/**
 * Performs actions when events are emitted
 */
abstract class Listener extends Addon
{
    /**
     * Mapping of event to method names to be registered
     * @var array
     */
    public $events = [];
}
