<?php

namespace Statamic\API;

class Taxonomy
{
    /**
     * Create a taxonomy
     *
     * @param $slug
     * @return \Statamic\Contracts\Data\Taxonomies\Taxonomy
     */
    public static function create($slug)
    {
        /** @var \Statamic\Contracts\Data\Taxonomies\Taxonomy $taxonomy */
        $taxonomy = app('Statamic\Contracts\Data\Taxonomies\Taxonomy');

        $taxonomy->path($slug);

        return $taxonomy;
    }
}