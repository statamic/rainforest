<?php

namespace Statamic\Extend;

use Illuminate\Console\Scheduling\Schedule;

/**
 * Repeatable tasks via cron
 */
abstract class Tasks extends Addon
{
    /**
     * Define the task schedule
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     */
    abstract public function schedule(Schedule $schedule);
}
