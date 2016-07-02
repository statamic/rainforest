<?php

namespace Statamic\Addons\Search\Commands;

use Statamic\API\Search;
use Statamic\Extend\Command;

class SearchCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'search:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the search index';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Search::update();

        $this->info('Search index updated!');
    }
}
