<?php

namespace Statamic\Data\Pages\File;

use Statamic\Data\Content\File\ContentFactory;
use Statamic\Contracts\Data\Pages\PageFactory as PageFactoryContract;

class PageFactory extends ContentFactory implements PageFactoryContract
{
    private $url;

    /**
     * @param string $url
     * @return $this
     */
    public function create($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return Page
     */
    public function get()
    {
        $page = new Page($this->url, $this->locale, $this->data);

        $page->order($this->order);
        $page->published($this->published);

        if (! $this->path) {
            $this->path = $page->path();
        }

        $page->dataType(pathinfo($this->path)['extension']);

        $page->originalPath($this->path);

        return $page;
    }
}
