<?php

namespace Statamic\Imaging;

use Exception;
use Statamic\API\Str;
use Statamic\API\URL;
use Statamic\API\Asset;
use Statamic\API\Config;
use League\Glide\Urls\UrlBuilderFactory;
use Statamic\Contracts\Imaging\UrlBuilder;

class GlideUrlBuilder implements UrlBuilder
{
    /**
     * Methods available in Glide's API
     *
     * @var array
     */
    private $api = [
        'or', 'crop', 'w', 'h', 'fit', 'dpr', 'bri', 'con', 'gam', 'sharp', 'blur', 'pixel', 'filt',
        'mark', 'markw', 'markx', 'marky', 'markpad', 'markpos', 'bg', 'border', 'q', 'fm'
    ];

    /**
     * ID of the asset
     *
     * @var string
     */
    private $id;

    /**
     * Path to the file
     *
     * @var string
     */
    private $path;

    /**
     * The vanity filename
     *
     * @var string
     */
    private $filename;

    /**
     * Parameters being built
     *
     * @var array
     */
    private $params = [];

    /**
     * Handle unknown method calls
     *
     * @param string $method
     * @param array $args
     * @return $this
     */
    public function __call($method, $args)
    {
        $this->setParam($method, $args[0]);

        return $this;
    }

    /**
     * Set a parameter
     *
     * @param string $param
     * @param mixed  $value
     * @throws \Exception
     */
    private function setParam($param, $value)
    {
        // Error out when given an unknown parameter.
        if (! in_array($param, $this->api)) {
            throw new Exception("Glide URL parameter [$param] does not exist.");
        }

        $this->params[$param] = $value;
    }

    /**
     * Set the parameters
     *
     * @param array $params
     * @return $this
     */
    public function params(array $params)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * Get all the parameters
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set the asset ID
     *
     * @param string $id
     * @return $this
     */
    public function id($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set the asset UUID
     *
     * @param string $uuid
     * @return $this
     * @deprecated
     */
    public function uuid($uuid)
    {
        return $this->id($uuid);
    }

    /**
     * Set the path of the asset
     *
     * @param string $path
     * @return mixed
     */
    public function path($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Set the filename
     *
     * @param  string $filename
     * @return $this
     */
    public function filename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function width($value)
    {
        $this->params['w'] = $value;

        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function height($value)
    {
        $this->params['h'] = $value;

        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function fit($value)
    {
        if ($value == 'crop_focal') {
            $value = 'crop';
            if ($asset = Asset::uuidRaw($this->id)) {
                if ($focus = $asset->get('focus')) {
                    $value .= '-' . $focus;
                }
            }
        }

        $this->params['fit'] = $value;

        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function crop($value)
    {
        $this->params['crop'] = $value;

        return $this;
    }

    /**
     * @param int $pixels
     * @return $this
     */
    public function square($pixels)
    {
        $this->params['w'] = $pixels;
        $this->params['h'] = $pixels;

        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function orient($value)
    {
        $this->params['or'] = $value;

        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function brightness($value)
    {
        $this->params['bri'] = $value;

        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function contrast($value)
    {
        $this->params['con'] = $value;

        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function gamma($value)
    {
        $this->params['gam'] = $value;

        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function sharpen($value)
    {
        $this->params['sharp'] = $value;

        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function blur($value)
    {
        $this->params['blur'] = $value;

        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function pixelate($value)
    {
        $this->params['pixel'] = $value;

        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function filter($value)
    {
        $this->params['filt'] = $value;

        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function quality($value)
    {
        $this->params['q'] = $value;

        return $this;
    }

    /**
     * Return the complete URL
     *
     * @return string
     * @throws \Exception
     */
    public function build()
    {
        if ($this->id) {
            $path = 'id/'.$this->id;
        } elseif ($this->path) {
            $path = $this->path;
        } else {
            throw new \Exception("Cannot build a Glide URL without a path or asset ID.");
        }

        $key = (Config::get('assets.image_manipulation_secure')) ? Config::getAppKey() : null;

        $builder = UrlBuilderFactory::create(Config::get('assets.image_manipulation_route'), $key);

        if ($this->filename) {
            $path .= Str::ensureLeft($this->filename, '/');
        }

        return URL::prependSiteRoot($builder->getUrl($path, $this->params));
    }
}
