<?php

namespace Statamic\Data\Taxonomies\File;

use Statamic\Data\Content\File\ContentFactory;
use Statamic\Contracts\Data\Taxonomies\TermFactory as TermFactoryContract;

class TermFactory extends ContentFactory implements TermFactoryContract
{
    protected $slug;
    protected $taxonomy;

    /**
     * @param string $slug
     * @return $this
     */
    public function create($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @param string $taxonomy
     * @return $this
     * @deprecated
     */
    public function taxonomy($taxonomy)
    {
        $this->taxonomy = $taxonomy;

        return $this;
    }

    /**
     * @return Term
     */
    public function get()
    {
        $term = new Term($this->slug, $this->taxonomy, $this->locale, $this->data);

        $term->originalPath($this->path ?: $term->path());

        return $term;
    }
}
