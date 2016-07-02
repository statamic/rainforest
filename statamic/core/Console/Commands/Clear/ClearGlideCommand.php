<?php

namespace Statamic\Console\Commands\Clear;

use Statamic\API\Folder;
use Illuminate\Console\Command;

class ClearGlideCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'clear:glide';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear the Glide image cache.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        Folder::delete(cache_path('glide'));

        $this->info('Your Glide image cache is now so very, very empty.');
    }
}
