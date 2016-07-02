<?php

namespace Statamic\Contracts\Data\Content;

use Statamic\Contracts\Data\Data;

interface Content extends Data
{
    /**
     * Get the content type
     *
     * @return string
     */
    public function contentType();

    /**
     * Get the unlocalized version of this content
     *
     * @return Content
     */
    public function unlocalized();

    /**
     * Is this localized (ie. not the default locale)
     *
     * @return mixed
     */
    public function isLocalized();

    /**
     * Get or set the locale
     *
     * @param string|null $locale
     * @return mixed
     */
    public function locale($locale = null);

    /**
     * Get or set the slug
     *
     * @param string|null $slug
     * @return mixed
     */
    public function slug($slug = null);

    /**
     * Get or set the URL
     *
     * @param string|null $url
     * @return mixed
     */
    public function url($url = null);

    /**
     * Get the URL path
     *
     * @return mixed
     */
    public function urlPath();

    /**
     * Get the absolute URL
     *
     * @return mixed
     */
    public function absoluteUrl();

    /**
     * Get the folder
     *
     * @return mixed
     */
    public function folder();

    /**
     * Get or set the order
     *
     * @param string|null $order
     * @return mixed
     */
    public function order($order = null);

    /**
     * Mark the content as published
     *
     * @return mixed
     */
    public function publish();

    /**
     * Mark the content as unpublished
     *
     * @return mixed
     */
    public function unpublish();

    /**
     * Set the published state
     *
     * @param bool|null $published
     * @return mixed
     */
    public function published($published = null);

    /**
     * Get or set the template
     *
     * @param string|null $template
     * @return mixed
     */
    public function template($template = null);

    /**
     * Get or set the layout
     *
     * @param string|null $layout
     * @return mixed
     */
    public function layout($layout = null);
}
