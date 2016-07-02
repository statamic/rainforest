<?php

namespace Statamic\Http;

use Statamic\API\Arr;
use Statamic\API\File;
use Statamic\API\Path;
use Statamic\DataStore;
use Statamic\API\Parse;
use Statamic\API\Config;
use Statamic\API\Helper;
use Statamic\API\Globals;

/**
 * Controls the rendering of templates in views
 */
class View
{
    /**
     * @var array|\Statamic\Contracts\Data\Content\Content
     */
    private $data;

    /**
     * @var string
     */
    private $template;

    /**
     * Create a new View instance
     *
     * @param \Statamic\DataStore $store
     */
    public function __construct(DataStore $store)
    {
        $this->store = $store;

        // Ensure the ViewFinder will look in the theme directory for templates
        app('view')->getFinder()->prependLocation(config('filesystems.disks.theme.root').'/templates');
    }

    /**
     * Render a template
     *
     * @param array|null  $data Additional data to be passed to the template
     * @param string|null $template Name of the template to load
     * @return string
     */
    public function render($data = [], $template = null)
    {
        $this->data = $data;

        $this->template = $template;

        // Render the first matching template for the view.
        foreach ($this->getTemplate() as $template) {
            if (view()->exists($template)) {
                $this->template = $template;
                $this->updateDataStore();
                start_measure('rendering', "Rendering Template [$template]");
                $view = view($template, $this->store->getAll());
                break;
            }
        }

        if (! isset($view)) {
            \Log::notice(sprintf("No matching templates: [%s]", join(', ', array_filter($this->getTemplate()))));

            return view('welcome');
        }

        stop_measure('rendering');

        return $view;
    }

    /**
     * Update the DataStore with necessary data
     */
    private function updateDataStore()
    {
        // Add some helper variables into the scope
        $this->store->merge([
            'template' => $this->template,
            'layout'   => $this->getLayout()
        ]);

        // Add globals. Each global set will get their own scope.
        foreach (Globals::getAll() as $global) {
            $env = $this->store->getEnvInScope('globals.'.$global->slug(), []);
            $data = $global->data();
            $data = Arr::combineRecursive($data, $env);
            $this->store->mergeInto($global->slug(), $data);
        }

        // The 'global.yaml' global set will be merged into the global cascade.
        $this->store->merge($this->store->getScope('global'));

        // Merge anything passed to this in a $data parameter.
        // Also put it in the 'page' scope if anyone ever needs to access it from inside another scope
        $data = (is_object($this->data)) ? $this->data->toArray() : $this->data;
        $this->store->merge($data);
        $this->store->mergeInto('page', $data);
    }

    /**
     * Get the template to be rendered
     *
     * @return array
     */
    private function getTemplate()
    {
        // First, if one was specified earlier, we'll just use that
        if ($this->template) {
            return Helper::ensureArray($this->template);
        }

        // Next, if it's a content object, we'll check that.
        if (is_object($this->data)) {
            if ($template = $this->data->template()) {
                return $template;
            }
        }

        // Not an object? We'll just check the array, and fall back to a default.
        return array_merge(
            Helper::ensureArray(array_get($this->data, 'template', [])),
            [Config::get('theming.default_page_template')]
        );
    }

    /**
     * Get the layout to be rendered
     *
     * @return array|string
     */
    private function getLayout()
    {
        // If it's a content object, we'll check that.
        if (is_object($this->data)) {
            if ($layout = $this->data->layout()) {
                return $layout;
            }
        }


        // If it's an error, we want to try to use an error layout first
        if ($template = $this->template) {
            if (Path::directory($template) == Config::get('theming.error_template_folder')) {
                if (File::disk('theme')->exists('layouts/error.html')) {
                    return 'error';
                }
            }
        }

        // Not an object? We'll just check the array, and fall back to a default.
        return array_get($this->data, 'layout', Config::get('theming.default_layout'));
    }
}
