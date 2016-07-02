<?php

namespace Statamic\Addons\Nav;

use Statamic\API\URL;
use Statamic\API\Str;
use Statamic\API\Content;
use Statamic\Extend\Tags;

class NavTags extends Tags
{
    /**
     * @var  \Statamic\Addons\Nav\TreeFactory
     */
    private $factory;

    /**
     * The {{ nav }} tag
     *
     * @return  string
     */
    public function index()
    {
        $this->factory = new TreeFactory($this->getParams());

        $tree = $this->factory->create();

        if ($tree) {
            $tree->sort($this->get('sort'));

            return $this->parseLoop($tree->toArray());
        }

        return null;
    }

    /**
     * The {{ nav:exists }} tag
     *
     * @return  string|null
     */
    public function exists()
    {
        $this->factory = new TreeFactory($this->getParams());

        $tree = $this->factory->create();

        if (! $tree) {
            return null;
        }

        return $this->parse([]);
    }

    /**
     * The {{ nav:count }} tag
     *
     * @return  string
     */
    public function count()
    {
        $this->factory = new TreeFactory($this->getParams());

        $tree = $this->factory->create();

        return $tree->count();
    }

    /**
     * Get the common parameters for the tags that require tree creation
     *
     * @return  array
     */
    private function getParams()
    {
        return [
            'from'         => Str::ensureLeft($this->get('from', URL::getCurrent()), '/'),
            'depth'        => $this->getInt('max_depth', 2),
            'unpublished'  => $this->getBool('show_unpublished', false),
            'entries'      => $this->getBool('include_entries', false),
            'sort'         => $this->get('sort'),
            'include_home' => $this->getBool('include_home'),
            'exclude'      => $this->getList('exclude'),
            'conditions'   => $this->getConditionParameters()
        ];
    }

    /**
     * Get parameters using the conditions syntax
     *
     * @return array
     */
    private function getConditionParameters()
    {
        return array_filter_key($this->parameters, function ($key) {
            return Str::contains($key, ':');
        });
    }

    /**
     * The {{ nav:breadcrumbs }} tag
     *
     * @return  string
     */
    public function breadcrumbs()
    {
        $crumbs = [];

        $url = $this->get(['url' , 'from'], URL::getCurrent());

        $segments = explode('/', $url);
        $segment_count = count($segments);
        $segments[0] = '/';

        // Create crumbs from segments
        for ($i = 1; $i <= $segment_count; $i++) {
            $segment_urls[] = URL::tidy(join($segments, '/'));
            array_pop($segments);
        }

        // Build up the content for each crumb
        foreach ($segment_urls as $segment_url) {
            if ($content = Content::get($segment_url)) {
                $crumbs[$segment_url] = $content;
                $crumbs[$segment_url]['is_current'] = (URL::getCurrent() == $segment_url);
            }
        }

        // Remove the homepage if requested
        if (! $this->getBool('include_home', true)) {
            array_pop($crumbs);
        }

        // Correct the order (unless they've requested it in reverse)
        if (! $this->getBool('reverse', false)) {
            $crumbs = array_reverse($crumbs);
        }

        // Parse the tag
        $output = $this->trim($this->getBool('trim', true))
                       ->parseLoop(array_values($crumbs));

        // Backspaces
        if ($backspaces = $this->getInt('backspace', 0)) {
            $output = substr($output, 0, -$backspaces);
        }

        return $output;
    }
}
