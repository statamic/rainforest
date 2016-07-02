<?php

namespace Statamic\Events;

use Statamic\Contracts\Stache\Cache;

class StacheUpdated extends Event
{
    /**
     * The Stache
     *
     * @var Cache
     */
    public $stache;

    /**
     * Was the content cache updated?
     *
     * @var bool
     */
    public $content;

    /**
     * Was the user cache updated?
     *
     * @var bool
     */
    public $users;

    /**
     * Was the asset cache updated?
     *
     * @var bool
     */
    public $assets;

    /**
     * Create a new event instance
     *
     * @param Cache $cache
     */
    public function __construct(Cache $cache)
    {
        $this->stache = $cache;

        $this->content = $cache->getContent()->hasBeenUpdated();

        $this->users = $cache->getUsers()->hasBeenUpdated();

        $this->assets = $cache->getAssets()->hasBeenUpdated();
    }
}
