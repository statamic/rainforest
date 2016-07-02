<?php

namespace Statamic\Addons\Search;

use Statamic\API\Cache;
use Statamic\API\Config;
use Statamic\API\Search;
use Statamic\Events\StacheUpdated;

class SearchListener extends \Statamic\Extend\Listener
{
    public $events = [
        'stache.updated' => 'handle'
    ];

    public function handle(StacheUpdated $event)
    {
        if (! Config::get('search.auto_index')) {
            return;
        }

        if ($event->content && ! Cache::get('search_index_idle', false) !== true) {
            Search::update();

            Cache::put('search_index_idle', true, Config::get('search.index_frequency'));
        }
    }
}
