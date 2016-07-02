<?php

namespace Statamic\Contracts\Data\Content;

interface ContentFactory
{
    /**
     * @return \Statamic\Contracts\Data\Content\Content
     */
    public function get();

    /**
     * @param array $data
     * @return $this
     */
    public function with(array $data);

    /**
     * @param $path
     * @return $this
     */
    public function path($path);

    /**
     * @param bool $published
     * @return $this
     */
    public function published($published);

    /**
     * @param mixed $order
     * @return $this
     */
    public function order($order);

    /**
     * @param string $locale
     * @return $this
     */
    public function locale($locale);
}
