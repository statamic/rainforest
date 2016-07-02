<?php

namespace Statamic\Providers;

use Statamic\API\Helper;
use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        'content.saved' => [
            'Statamic\Stache\Listeners\UpdateContent',
        ],
    ];

    public function register()
    {
        //
    }

    public function boot(DispatcherContract $dispatcher)
    {
        parent::boot($dispatcher);

        /** @var \Statamic\Repositories\AddonRepository $repo */
        $repo = app('Statamic\Repositories\AddonRepository');

        // We only care about the listener classes
        $listeners = $repo->filter('Listener.php')->getClasses();

        // Register all the events specified in each listener class
        foreach ($listeners as $class) {
            $listener = new $class;

            foreach ($listener->events as $event => $methods) {
                foreach (Helper::ensureArray($methods) as $method) {
                    $dispatcher->listen($event, [$listener, $method]);
                }
            }
        }
    }
}
