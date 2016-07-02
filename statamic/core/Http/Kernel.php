<?php

namespace Statamic\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * @var array
     */
    protected $middleware = [
        'Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode',
        'Statamic\Http\Middleware\CheckForStaticallyCachedPage',
        'locale' => 'Statamic\Http\Middleware\CP\DefaultLocale',
        'Illuminate\Cookie\Middleware\EncryptCookies',
        'Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse',
        'Illuminate\Session\Middleware\StartSession',
        'Illuminate\View\Middleware\ShareErrorsFromSession',
//        'Statamic\Http\Middleware\VerifyCsrfToken',
        'Statamic\Http\Middleware\Outpost',
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => 'Statamic\Http\Middleware\CP\Authenticate',
        'start' => 'Statamic\Http\Middleware\CP\StartPage',
        'configurable' => 'Statamic\Http\Middleware\CP\Configurable',
        'installer' => 'Statamic\Http\Middleware\Installer',
    ];
}
