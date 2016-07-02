<?php

namespace Statamic\Console\Commands;

use Statamic\API\Config;
use Illuminate\Console\Command;

class SetCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'set';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'set {setting} {value}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign a new value to a setting.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $setting = $this->argument('setting');

        Config::set($setting, $this->argument('value'));
        Config::save();

        $this->info($setting . ' has been set!');
    }
}
