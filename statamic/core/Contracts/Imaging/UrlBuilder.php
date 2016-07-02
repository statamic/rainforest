<?php

namespace Statamic\Contracts\Imaging;

interface UrlBuilder
{
    /**
     * Set the ID of the asset
     *
     * @param string $id
     * @return mixed
     */
    public function id($id);

    /**
     * Set the path of the asset
     *
     * @param string $path
     * @return mixed
     */
    public function path($path);

    /**
     * Return the complete URL
     *
     * @return string
     */
    public function build();
}
