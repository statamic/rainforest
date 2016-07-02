<?php

namespace Statamic\CP;

use Statamic\API\URL;
use Statamic\API\File;
use Statamic\API\Path;
use Statamic\API\YAML;
use Statamic\API\Str;

class Router
{
    /**
     * Merge all addon routes into the Laravel router
     */
    public function bindAddonRoutes()
    {
        $files = addon_repo()->filter('routes.yaml')->getFiles();

        // Register all the routes in each yaml file
        foreach ($files as $path) {
            $yaml = YAML::parse(File::get($path));

            foreach (array_get($yaml, 'routes', []) as $route => $action) {
                $verb = 'get';

                if (Str::contains($route, '@')) {
                    list($verb, $route) = explode('@', $route);
                }

                $route = URL::assemble(CP_ROUTE, 'addons', Str::slug(Path::folder($path)), $route);

                if (is_string($action)) {
                    $action = ['uses' => $action];
                }

                $action['uses'] = $this->getController($path) . '@' . $action['uses'];

                $verb($route, $action);
            }
        }
    }

    /**
     * Get the controller class name
     *
     * @param string $path
     * @return string
     */
    private function getController($path)
    {
        $name = Path::folder($path);

        $namespace = preg_replace('/^(?:site\/addons|statamic\/bundles)/', 'Statamic\\Addons', $path);

        $namespace = str_replace(['/', '\routes.yaml'], ['\\', ''], $namespace);

        return $namespace . '\\' . $name . 'Controller';
    }
}
