<?php

namespace Statamic\API;

class Image
{
    /**
     * Get a URL builder instance to continue chaining, or a URL right away if provided with params.
     *
     * @param null|string $item
     * @param null|array  $params
     * @return string|\Statamic\Imaging\GlideUrlBuilder
     */
    public static function manipulate($item = null, $params = null)
    {
        /** @var \Statamic\Imaging\GlideUrlBuilder $builder */
        $builder = app('Statamic\Contracts\Imaging\UrlBuilder');

        if (Str::isUrl($item)) {
            $builder->path($item);
        } else {
            $builder->id($item);
        }

        if ($params) {
            return $builder->params($params)->build();
        }

        return $builder;
    }
}
