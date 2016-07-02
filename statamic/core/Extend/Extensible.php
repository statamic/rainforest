<?php

namespace Statamic\Extend;

use Statamic\API\URL;
use Statamic\API\File;
use Statamic\API\Path;
use Statamic\API\YAML;
use Statamic\API\Email;
use Statamic\API\Str;
use ReflectionException;
use Statamic\Extend\Contextual\ContextualJs;
use Statamic\Extend\Contextual\ContextualCss;
use Statamic\Exceptions\ApiNotFoundException;
use Statamic\Extend\Contextual\ContextualBlink;
use Statamic\Extend\Contextual\ContextualCache;
use Statamic\Extend\Contextual\ContextualFlash;
use Statamic\Extend\Contextual\ContextualImage;
use Statamic\Extend\Contextual\ContextualCookie;
use Statamic\Extend\Contextual\ContextualStorage;
use Statamic\Extend\Contextual\ContextualSession;
use Statamic\Extend\Contextual\ContextualResource;

trait Extensible
{
    /**
     * Name of the addon
     * @var string
     */
    public $addon_name;

    /**
     * Type of addon. Tags, etc
     * @var string
     */
    public $addon_type;

    /**
     * @var  \Statamic\Extend\Contextual\ContextualBlink
     */
    protected $blink;

    /**
     * @var  \Statamic\Extend\Contextual\ContextualCache
     */
    protected $cache;

    /**
     * @var  \Statamic\Extend\Contextual\ContextualStorage
     */
    protected $storage;

    /**
     * @var  \Statamic\Extend\Contextual\ContextualSession
     */
    protected $session;

    /**
     * @var  \Statamic\Extend\Contextual\ContextualCookie
     */
    protected $cookie;

    /**
     * @var  \Statamic\Extend\Contextual\ContextualFlash
     */
    protected $flash;

    /**
     * @var  \Statamic\Extend\Contextual\ContextualResource
     */
    public $resource;

    /**
     * @var  \Statamic\Extend\Contextual\ContextualCss
     */
    public $css;

    /**
     * @var  \Statamic\Extend\Contextual\ContextualJs
     */
    public $js;

    /**
     * @var  \Statamic\Extend\Contextual\ContextualImage
     */
    public $img;


    /**
     * Build up the addon instance
     *
     * @param null|string $name  The name of the addon, if not inheriting.
     */
    private function buildAddon($name = null)
    {
        $this->addon_name = $name ?: $this->parseAddonName();
        $this->addon_type = $this->parseAddonType();

        $addon = ($this instanceof Addon) ? $this : new Addon($name);

        $this->blink = new ContextualBlink($addon);
        $this->cache = new ContextualCache($addon);
        $this->storage = new ContextualStorage($addon);
        $this->session = new ContextualSession($addon);
        $this->cookie = new ContextualCookie($addon);
        $this->flash = new ContextualFlash($addon);
        $this->resource = new ContextualResource($addon);
        $this->css = new ContextualCss($addon);
        $this->js = new ContextualJs($addon);
        $this->img = new ContextualImage($addon);
    }

    /**
     * Load the addon's bootstrap file, if available.
     * Useful for an addon to use a composer autoloader, for example.
     */
    private function bootstrap()
    {
        $reflector = new \ReflectionClass(static::class);

        $path = Path::directory($reflector->getFileName()) . '/bootstrap.php';

        if (File::exists($path)) {
            require_once $path;
        }
    }

    /**
     * Initialize the addon without needing to re-construct the class
     */
    protected function init()
    {

    }

    /**
     * Parses the name of the plugin from the class
     *
     * @return string
     */
    private function parseAddonName()
    {
        if ($this->addon_name) {
            return $this->addon_name;
        }

        return $this->addon_name = explode('\\', get_called_class())[2];
    }

    /**
     * Get the name of the addon, uncustomized by meta.yaml
     *
     * @return string
     */
    public function getAddonClassName()
    {
        return $this->addon_name;
    }

    /**
     * Get the fully qualified class name of the appropriate addon aspect
     *
     * @return string
     */
    public function getAddonFQCN()
    {
        return get_called_class();
    }

    /**
     * Get the name of the addon, which might be customized in meta.yaml
     *
     * @return mixed|string
     */
    public function getAddonName()
    {
        if ($name = array_get($this->getMeta(), 'name')) {
            return $name;
        }

        return $this->getAddonClassName();
    }

    /**
     * Gets the type of plugin
     *
     * @return string
     */
    private function parseAddonType()
    {
        $class_bits = explode('\\', get_called_class());
        $class = last($class_bits);
        $split = preg_split('/(?<=[a-z])(?=[A-Z])|(?<=[A-Z])(?=[A-Z][a-z])/', $class);

        return end($split);
    }

    /**
     * Get the meta information
     *
     * @return array
     */
    public function getMeta()
    {
        $reflector = new \ReflectionClass(get_called_class());
        $file = pathinfo($reflector->getFileName())['dirname'] . '/meta.yaml';

        if (! File::exists($file)) {
            return [];
        }

        return YAML::parse(File::get($file));
    }

    /**
     * Emit a namespaced event
     *
     * @param string $event  Name of the event
     * @param mixed  $payload  Data to send with the event
     * @return mixed
     */
    public function emitEvent($event, $payload)
    {
        return event($this->addon_name . '.' . $event, $payload);
    }

    /**
     * Access the API class of a $addon
     *
     * @param string|null $addon Name of the addon
     * @return mixed The API class for the addon, if it exists
     * @throws \Statamic\Exceptions\ApiNotFoundException
     */
    public function api($addon = null)
    {
        $addon = $addon ?: $this->getAddonClassName();

        try {
            return addon($addon);
        } catch (ReflectionException $e) {
            throw new ApiNotFoundException("No such class [{$addon}API]");
        }
    }

    /**
     * Retrieves a config variable, or the whole array
     *
     * @param null|string|array $keys Keys of parameter to return
     * @param mixed $default  Default value to return if not set
     * @return mixed
     */
    public function getConfig($keys = null, $default = null)
    {
        $config = datastore()->getScope('addons.' . Str::snake($this->addon_name));

        if (is_null($keys)) {
            return $config;
        }

        if (! is_array($keys)) {
            $keys = [$keys];
        }

        foreach ($keys as $key) {
            if ($value = array_get($config, $key)) {
                return $value;
            }
        }

        return $default;
    }

    /**
     * Same as $this->getConfig(), but treats as a boolean
     *
     * @param string|array $keys
     * @param null         $default
     * @return bool
     */
    public function getConfigBool($keys, $default = null)
    {
        return bool($this->getConfig($keys, $default));
    }

    /**
     * Same as $this->getConfig(), but treats as an integer
     *
     * @param string|array $keys
     * @param null         $default
     * @return bool
     */
    public function getConfigInt($keys, $default = null)
    {
        return int($this->getConfig($keys, $default));
    }

    /**
     * Get the directory this addon file is in
     *
     * @return string
     */
    protected function getDirectory()
    {
        $reflector = new \ReflectionClass($this->getAddonFQCN());

        return Path::popLastSegment($reflector->getFileName());
    }

    /**
     * Create an email and automatically set the path to the views
     *
     * @return \Statamic\Email\Builder
     */
    protected function email()
    {
        $email = Email::create();

        $email->in($this->getDirectory() . '/views');

        return $email;
    }

    /**
     * Generate an event url
     *
     * @param string $url
     * @param bool $relative
     * @return string
     */
    protected function eventUrl($url, $relative = true)
    {
        $url = URL::tidy(
            URL::prependSiteUrl(EVENT_ROUTE . '/' . $this->getAddonClassName() . '/' . $url)
        );

        if ($relative) {
            $url = URL::makeRelative($url);
        }

        return $url;
    }

    protected function trans($key)
    {
        return trans('addons.'.$this->getAddonClassName().'::'.$key);
    }

    protected function transChoice($key, $number)
    {
        return trans_choice('addons.'.$this->getAddonClassName().'::'.$key, $number);
    }

    /**
     * Render a Blade view from within the addon's views directory
     *
     * @param string $view  Name of the view
     * @param array  $data  Data to pass into the view
     * @return \Illuminate\View\View
     */
    public function view($view, $data = [])
    {
        $reflector = new \ReflectionClass($this);

        $directory = Path::directory($reflector->getFileName()) . '/resources/views';

        $namespace = $this->getAddonClassName();

        app('view')->getFinder()->addNamespace($namespace, $directory);

        return view($namespace.'::'.$view, $data);
    }
}
