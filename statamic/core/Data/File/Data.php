<?php

namespace Statamic\Data\File;

use Statamic\API\Parse;
use Statamic\API\Config;
use Stringy\StaticStringy as Stringy;
use Illuminate\Contracts\Support\Arrayable;
use Statamic\Exceptions\UuidExistsException;
use Statamic\Contracts\Data\Data as DataContract;

/**
 * The abstract data type
 */
abstract class Data implements DataContract
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var array
     */
    protected $supplements = [];

    /**
     * @var string
     */
    protected $extension;

    /**
     * Create a new Data object
     *
     * @param array  $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get or set the identifier
     *
     * @param string|null $id
     * @return mixed
     */
    abstract public function id($id = null);

    /**
     * Ensure there is a UUID
     *
     * @param bool $save  Whether the file get saved once a UUID is generated
     */
    public function ensureId($save = false)
    {
        try {
            $this->id(true);

            if ($save) {
                $this->save(false);
            }
        } catch (UuidExistsException $e) {
            // It's already has a UUID, do nothing.
        }
    }

    /**
     * Get or set the data
     *
     * @param array|null $data
     * @return mixed
     */
    public function data($data = null)
    {
        if (is_null($data)) {
            return $this->data;
        }

        $this->data = $data;
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
        return array_get($this->data, $key, $default);
    }

    /**
     * Set a value in the front matter
     *
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        array_set($this->data, $key, $value);
    }

    /**
     * Remove a key in the front matter
     *
     * @param string $key
     */
    public function remove($key)
    {
        unset($this->data[$key]);
    }

    /**
     * Get the data, processed by its fieldtypes
     *
     * @return array
     */
    public function processedData()
    {
        $data = $this->data();

        $fieldtypes = collect($this->fieldset()->fieldtypes())->keyBy(function($fieldtype) {
            return $fieldtype->getFieldConfig('name');
        });

        foreach ($data as $field_name => $field_data) {
            if ($fieldtype = $fieldtypes->get($field_name)) {
                $data[$field_name] = $fieldtype->preProcess($field_data);
            }
        }

        return $data;
    }

    /**
     * Get the content
     *
     * @param string|null $content Content to set
     * @return mixed
     */
    public function content($content = null)
    {
        if (is_null($content)) {
            return array_get($this->data, 'content');
        }

        $this->data['content'] = $content;
    }

    /**
     * Get or set the data type (extension)
     *
     * @param string|null $type
     * @return mixed
     */
    public function dataType($type = null)
    {
        if (! is_null($type)) {
            $this->extension = $type;
        }

        if ($this->extension) {
            return $this->extension;
        }

        return array_get(pathinfo($this->path()), 'extension');
    }

    /**
     * Parses the content as their content type, smartypants, and as a template
     *
     * @return mixed|string
     */
    public function parseContent()
    {
        $content = $this->content();

        switch ($this->dataType()) {
            case 'markdown':
            case 'md':
                $content = markdown($content);
                break;

            case 'text':
            case 'txt':
                $content = nl2br(strip_tags($content));
                break;

            case 'textile':
                $content = textile($content);
        }

        if (Config::get('theming.smartypants')) {
            $content = smartypants($content);
        }

        if (! $this->get('parse_content', true)) {
            $content = Stringy::replace($content, '{', '&lbrace;');
            $content = Stringy::replace($content, '}', '&rbrace;');
        }

        $data = array_merge(datastore()->getAll(), $this->data);

        return Parse::template($content, $data);
    }

    /**
     * Get the supplemented data
     *
     * @return array
     */
    public function supplements()
    {
        return $this->supplements;
    }

    /**
     * Get a key in the supplemental data
     *
     * @param string     $key     Key to retrieve
     * @param mixed|null $default Fallback data
     * @return mixed
     */
    public function getSupplement($key, $default = null)
    {
        return array_get($this->supplements, $key, $default);
    }

    /**
     * Set a key in the supplemental data
     *
     * @param string $key   Key to set
     * @param mixed  $value Value to set
     * @return mixed
     */
    public function setSupplement($key, $value)
    {
        $this->supplements[$key] = $value;
    }

    /**
     * Remove a key from the supplemental data
     *
     * @param string $key Key to remove
     * @return mixed
     */
    public function removeSupplement($key)
    {
        unset($this->supplements[$key]);
    }

    /**
     * Convert this to an array (for use in templates)
     *
     * @return array
     */
    public function toArray()
    {
        $this->supplement();

        $content_raw = $this->content();
        $content = $this->parseContent();

        return array_merge(
            $this->supplements,
            $this->data(),
            compact('content', 'content_raw')
        );
    }

    /**
     * Save the data
     *
     * @return mixed
     */
    abstract public function save();

    /**
     * Rename the data
     *
     * @return mixed
     */
    abstract protected function rename();

    /**
     * Delete the data
     *
     * @return mixed
     */
    abstract public function delete();
}
