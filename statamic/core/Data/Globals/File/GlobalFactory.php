<?php

namespace Statamic\Data\Globals\File;

use Statamic\Data\Content\File\ContentFactory;
use Statamic\Contracts\Data\Globals\GlobalFactory as GlobalFactoryContract;

class GlobalFactory extends ContentFactory implements GlobalFactoryContract
{
    protected $slug;

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
     * @return GlobalContent
     */
    public function get()
    {
        $global = new GlobalContent($this->slug, $this->locale, $this->data);

        if (! $this->path) {
            $this->path = $global->path();
        }

        $global->originalPath($this->path);

        return $global;
    }
}
