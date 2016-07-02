<?php

namespace Statamic\Data\Content\File;

use Statamic\API\URL;
use Statamic\API\File;
use Statamic\API\Path;
use Statamic\API\YAML;
use Statamic\API\Config;
use Statamic\API\Helper;
use Statamic\Data\File\Data;
use Statamic\Contracts\Data\Pages\Page;
use Statamic\Contracts\Data\Entries\Entry;
use Statamic\Exceptions\UuidExistsException;
use Statamic\Contracts\Data\Taxonomies\Term;
use Statamic\Contracts\Data\Globals\GlobalContent;
use Statamic\Contracts\Data\Content\Content as ContentContract;

/**
 * An abstract content data type. The Page and Entry objects both extend this.
 */
abstract class Content extends Data implements ContentContract
{
    /**
     * @var string
     */
    protected $locale;

    /**
     * @var string
     */
    protected $original_path;

    /**
     * @var string|int
     */
    protected $order;

    /**
     * @var bool
     */
    protected $published = true;

    /**
     * Create a new Content object
     *
     * @param null|string $locale
     * @param array       $front_matter
     */
    public function __construct($locale = null, $front_matter = [])
    {
        parent::__construct($front_matter);

        $this->locale = $locale ?: site_locale();
    }

    /**
     * Get the content type
     *
     * @return string
     */
    public function contentType()
    {
        if ($this instanceof Page) {
            return 'page';
        } elseif ($this instanceof Entry) {
            return 'entry';
        } elseif ($this instanceof Term) {
            return 'term';
        } elseif ($this instanceof GlobalContent) {
            return 'globals';
        }
    }

    /**
     * Get a key from the data
     *
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $data = $this->data;

        // Merge with default/unlocalized data
        if ($this->locale !== Config::getDefaultLocale()) {
            $default_content = $this->unlocalized();
            $data = array_merge($default_content->data(), $data);
        }

        // Merge with cascading/folder data
        $data = array_merge($this->getFolderData(), $data);

        return array_get($data, $key, $default);
    }

    /**
     * Get data from the folder.yaml
     *
     * @return array
     */
    abstract protected function getFolderData();

    /**
     * Get or set the ID
     *
     * @param string|bool|null $id
     * @return string
     */
    public function id($id = null)
    {
        if (is_null($id)) {
            return array_get($this->data, 'id');
        }

        $this->setId($id);
    }

    /**
     * Set the ID
     *
     * @param string|bool $id
     * @throws \Statamic\Exceptions\UuidExistsException
     */
    protected function setId($id)
    {
        if ($this->id()) {
            throw new UuidExistsException('Data already has a ID');
        }

        // If true is passed in, we'll generate a UUID. Otherwise just use what was passed.
        $this->data['id'] = ($id === true) ? Helper::makeUuid() : $id;
    }

    /**
     * Get or set the slug
     *
     * @param string|null $slug
     * @return mixed
     */
    public function slug($slug = null)
    {
        if (is_null($slug)) {
            return $this->getSlug();
        }

        $this->setSlug($slug);
    }

    abstract protected function getSlug();
    abstract protected function setSlug($slug);

    /**
     * Get or set the path to the data
     *
     * @param string|null $path Path to set
     * @return mixed
     */
    public function path($path = null)
    {
        if (is_null($path)) {
            return $this->getPath();
        }

        $this->setPath($path);
    }

    abstract protected function getPath();
    abstract protected function setPath($path);

    /**
     * Gets or sets the original path of the file
     *
     * This should be called after creating a data object so we can know what the
     * path *was* when it comes time to renaming/saving a new file. The path is
     * built dynamically so we need to keep track of the original somehow.
     *
     * @param string|null $path
     */
    public function originalPath($path = null)
    {
        if (is_null($path)) {
            return $this->original_path;
        }

        $this->original_path = $path;
    }

    /**
     * Get or set the locale
     *
     * @param string|null $locale
     * @return string
     */
    public function locale($locale = null)
    {
        if (is_null($locale)) {
            return $this->locale;
        }

        $this->locale = $locale;
    }

    /**
     * Is this content localized?
     *
     * @return bool
     */
    public function isLocalized()
    {
        return $this->locale !== default_locale();
    }

    /**
     * Get the full, absolute URL
     */
    public function absoluteUrl()
    {
        return URL::prependSiteUrl($this->urlPath());
    }

    /**
     * Get or set the URL
     *
     * @param string|null $url
     * @return mixed
     */
    public function url($url = null)
    {
        return parse_url($this->absoluteUrl())['path'];
    }

    /**
     * Get or set the order key
     *
     * @param mixed|null $order
     * @return mixed
     */
    public function order($order = null)
    {
        if (is_null($order)) {
            return $this->order;
        }

        $this->order = $order;
    }

    /**
     * Get or set the content
     *
     * @param string|null $content
     * @return mixed
     */
    public function content($content = null)
    {
        if (is_null($content)) {
            return $this->getContent();
        }

        $this->data['content'] = $content;
    }

    /**
     * Get the content
     *
     * @return mixed
     */
    public function getContent()
    {
        if ($content = array_get($this->data, 'content')) {
            return $content;
        }

        return ($this->locale !== Config::getDefaultLocale())
            ? $this->unlocalized()->content()
            : null;
    }

    /**
     * Get the folder of the file relative to content path
     *
     * @return string
     */
    public function folder()
    {
        $dir = Path::directory($this->path());

        $dir = preg_replace('#^(collections|pages|taxonomies)/#', '', $dir);

        if ($this instanceof Page) {
            return $dir;
        }

        return (str_contains($dir, '/')) ? explode('/', $dir)[0] : $dir;
    }

    /**
     * Get or set the publish status
     *
     * @param null|bool $published
     * @return void|bool
     */
    public function published($published = null)
    {
        if (is_null($published)) {
            return $this->published;
        }

        $this->published = $published;
    }

    /**
     * Publish the content
     */
    public function publish()
    {
        $this->published = true;
    }

    /**
     * Unpublishes the content
     */
    public function unpublish()
    {
        $this->published = false;
    }

    /**
     * Get or set the fieldset
     *
     * @param string|null $fieldset
     */
    public function fieldset($fieldset = null)
    {
        if (is_null($fieldset)) {
            return $this->getFieldset();
        }

        $this->setFieldset($fieldset);
    }

    /**
     * Set the fieldset
     *
     * @param string $fieldset
     */
    protected function setFieldset($fieldset)
    {
        $this->set('fieldset', $fieldset);
    }

    /**
     * Add supplemental data to the attributes
     */
    public function supplement()
    {
        $this->supplements['slug']      = $this->slug();
        $this->supplements['url']       = $this->url();
        $this->supplements['url_path']  = $this->urlPath();
        $this->supplements['permalink'] = $this->absoluteUrl();
        $this->supplements['edit_url']  = $this->editUrl();
        $this->supplements['published'] = $this->published();
        $this->supplements['order']     = $this->order();

        // If the file isn't found, it's probably temporary content created during a sneak peek.
        try {
            $this->supplements['last_modified'] = File::disk('content')->lastModified($this->path());
        } catch (\League\Flysystem\FileNotFoundException $e) {
            $this->supplements['last_modified'] = time();
        }
    }

    /**
     * @return PathBuilder
     */
    protected function pathBuilder()
    {
        return app('Statamic\Contracts\Data\Content\PathBuilder');
    }

    /**
     * Get or set the template
     *
     * @param string|null $template
     * @return mixed
     */
    public function template($template = null)
    {
        if (is_null($template)) {
            return $this->getTemplate();
        }

        $this->setTemplate($template);
    }

    abstract protected function getTemplate();
    abstract protected function setTemplate($template);

    /**
     * Get or set the layout
     *
     * @param string|null $layout
     * @return mixed
     */
    public function layout($layout = null)
    {
        if (is_null($layout)) {
            return $this->getLayout();
        }

        $this->setLayout($layout);
    }

    abstract protected function getLayout();
    abstract protected function setLayout($layout);

    /**
     * Save the data
     *
     * @return mixed
     */
    public function save()
    {
        // Remove any localized data that is the same as the unlocalized version
        if ($this->isLocalized()) {
            $unlocalized_data = $this->unlocalized()->data();

            foreach ($this->data() as $key => $value) {
                // Ignore some keys
                if (in_array($key, ['id'])) {
                    continue;
                }

                if ($value === array_get($unlocalized_data, $key)) {
                    $this->remove($key);
                }
            }
        }

        $data = $this->data();
        $content = array_get($this->data, 'content');

        if ($content || $content == '') {
            unset($data['content']);
        }

        $contents = YAML::dump($data, $content);

        File::disk('content')->put($this->getPath(), $contents);

        // Has this been renamed?
        if ($this->getPath() !== $this->original_path) {
            $this->rename();
        }

        event('content.saved', $this);
    }
}
