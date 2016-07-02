<?php

namespace Statamic\Testing;

use Statamic\API\Stache;

class TestCase extends \Illuminate\Foundation\Testing\TestCase
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../../bootstrap/app.php';

        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

        $this->loadStache();

        return $app;
    }

    private function loadStache()
    {
        if (! Stache::exists()) {
            Stache::update();
        } else {
            Stache::load();
        }
    }
}
