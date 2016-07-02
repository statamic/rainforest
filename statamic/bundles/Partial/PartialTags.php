<?php

namespace Statamic\Addons\Partial;

use Statamic\API\Path;
use Statamic\API\File;
use Statamic\API\Parse;
use Statamic\API\Config;
use Statamic\Extend\Tags;

class PartialTags extends Tags
{
    public function __call($method, $arguments)
    {
        // We pass the original non-studly case value in as
        // an argument, but fall back to the studly version just in case.
        $src = $this->get('src', array_get($arguments, 0, $method));

        $template = File::disk('theme')->get("partials/{$src}.html");

        // Allow parameters to be variable names and retrieve them from
        // context. If they don't exist, they fall back to the string.
        $variables = [];
        foreach ($this->parameters as $key => $param) {
            $variables[$key] = array_get($this->context, $param, $param);
        }

        $variables = array_merge($variables, $this->context);

        return Parse::template($template, $variables);
    }
}
