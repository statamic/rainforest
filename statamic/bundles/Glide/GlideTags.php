<?php

namespace Statamic\Addons\Glide;

use Statamic\API\Str;
use Statamic\API\Asset;
use Statamic\API\Image;
use Statamic\Extend\Tags;
use Statamic\Imaging\ImageGenerator;

class GlideTags extends Tags
{
    /**
     * Maps to {{ glide:[field] }}
     *
     * Where `field` is the variable containing the image ID
     *
     * @param  $method
     * @param  $args
     * @return string
     */
    public function __call($method, $args)
    {
        $tag = explode(':', $this->tag, 2)[1];

        $id = array_get($this->context, $tag);

        return $this->output($this->generateGlideUrl($id));
    }

    /**
     * Maps to {{ glide }}
     *
     * Alternate syntax, where you pass the ID or path directly as a parameter or tag pair content
     *
     * @return string
     */
    public function index()
    {
        $item = ($this->content)
            ? $this->parse([])
            : $this->get(['src', 'id', 'path']);

        return $this->output($this->generateGlideUrl($item));
    }

    /**
     * Maps to {{ glide:generate }} ... {{ /glide:generate }}
     *
     * Generates the image and makes variables available within the pair.
     *
     * @return string
     */
    public function generate()
    {
        $item = $this->get(['src', 'id', 'path']);

        $url = $this->generateGlideUrl($item);

        $path = $this->generateImage($item);

        list($width, $height) = getimagesize($path);

        return $this->parse(
            compact('url', 'width', 'height')
        );
    }

    /**
     * Generate the image
     *
     * @param string $item  Either a path or an asset ID
     * @return string       Path to the generated image
     */
    private function generateImage($item)
    {
        $generator = new ImageGenerator(app('League\Glide\Server'));

        $params = $this->getGlideParams($item);

        $path = (Str::isUrl($item))
            ? $generator->generateByPath($item, $params)
            : $generator->generateByAsset(Asset::uuidRaw($item), $params);

        return cache_path('glide/'.$path);
    }

    /**
     * Output the tag
     *
     * @param string $url
     * @return string
     */
    private function output($url)
    {
        if ($this->getBool('tag')) {
            return "<img src=\"$url\" alt=\"{$this->get('alt')}\" />";
        }

        return $url;
    }

    /**
     * The URL generation
     *
     * @param  string $item  Either the ID or path of the image.
     * @return string
     */
    private function generateGlideUrl($item)
    {
        return $this->getUrlBuilder($item)->build();
    }

    /**
     * Get the raw Glide parameters
     *
     * @param string|null $item
     * @return array
     */
    private function getGlideParams($item = null)
    {
        return $this->getUrlBuilder($item)->getParams();
    }

    /**
     * Get the Glide URL builder with the parameters added to it
     *
     * @param string|null $item
     * @return \Statamic\Imaging\GlideUrlBuilder
     */
    private function getUrlBuilder($item = null)
    {
        $builder = Image::manipulate($item);

        $this->getManipulationParams()->each(function ($value, $param) use ($builder) {
            $builder->$param($value);
        });

        return $builder;
    }

    /**
     * Get the tag parameters applicable to image manipulation
     *
     * @return \Illuminate\Support\Collection
     */
    private function getManipulationParams()
    {
        $params = collect();

        foreach ($this->parameters as $param => $value) {
            if (! in_array($param, ['src', 'id', 'path', 'tag', 'alt'])) {
                $params->put($param, $value);
            }
        }

        return $params;
    }
}
