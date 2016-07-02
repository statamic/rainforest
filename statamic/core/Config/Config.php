<?php

namespace Statamic\Config;

use Statamic\Contracts\Config\Config as ConfigContract;

abstract class Config implements ConfigContract
{
    /**
     * @var array
     */
    protected $original;

    /**
     * @var array
     */
    protected $config;

    /**
     * Populate the config object with data
     *
     * @param array $config
     */
    public function hydrate(array $config)
    {
        $this->original = $this->config = $config;
    }

    /**
     * Get a config value
     *
     * @param string $key
     * @param bool   $default
     * @return mixed
     */
    public function get($key, $default = false)
    {
        return array_get($this->config, $key, $default);
    }

    /**
     * Set a config value
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set($key, $value)
    {
        array_set($this->config, $key, $value);
    }

    /**
     * Get all config values
     *
     * @return array
     */
    public function all()
    {
        return $this->config;
    }
}