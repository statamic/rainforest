<?php

namespace Statamic\Providers;

use Statamic\Forms\Form;
use Statamic\Forms\Formset;
use Statamic\Forms\Submission;
use Illuminate\Support\ServiceProvider;

class FormServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('Statamic\Contracts\Forms\Form', function () {
            return new Form;
        });

        $this->app->bind('Statamic\Contracts\Forms\Formset', function () {
            return new Formset;
        });

        $this->app->bind('Statamic\Contracts\Forms\Submission', function () {
            return new Submission;
        });
    }
}
