<?php

namespace Statamic\Data\Content\File;

use Statamic\Contracts\Data\Content\ContentFactory as ContentFactoryContract;

abstract class ContentFactory implements ContentFactoryContract
{
    protected $data = [];
    protected $path;
    protected $published = true;
    protected $order;
    protected $locale;

    /**
     * @param array $data
     * @return $this
     */
    public function with(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param $path
     * @return $this
     */
    public function path($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @param bool $published
     * @return $this
     */
    public function published($published)
    {
        $this->published = $published;

        return $this;
    }

    /**
     * @param mixed $order
     * @return $this
     */
    public function order($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @param string $locale
     * @return $this
     */
    public function locale($locale)
    {
        $this->locale = $locale;

        return $this;
    }
}
