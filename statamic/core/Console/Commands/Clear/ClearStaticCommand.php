<?php

namespace Statamic\Console\Commands\Clear;

use Illuminate\Console\Command;

class ClearStaticCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'clear:static';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear the Static Page Cache.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        addon('StaticPageCache')->clear();

        $this->info('Your static page cache is now so very, very empty.');
    }
}
