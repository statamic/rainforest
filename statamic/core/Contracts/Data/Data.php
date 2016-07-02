<?php

namespace Statamic\Contracts\Data;

use Statamic\Contracts\CP\Editable;
use Illuminate\Contracts\Support\Arrayable;

interface Data extends Arrayable, Editable
{
    /**
     * Get or set the identifier
     *
     * @param string|null $id
     * @return mixed
     */
    public function id($id = null);

    /**
     * Ensure there is an identifier
     *
     * @param bool $save Whether or not to save
     * @return mixed
     */
    public function ensureId($save = false);

    /**
     * Get or set the data
     *
     * @param array|null $data
     * @return mixed
     */
    public function data($data = null);

    /**
     * Get the data pre-processed by it's fieldtypes
     *
     * @return array
     */
    public function processedData();

    /**
     * Get a key from the data
     *
     * @param string     $key     Key to retrieve
     * @param mixed|null $default Fallback value
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * Set a key in the data
     *
     * @param string $key   Key to set
     * @param mixed  $value
     */
    public function set($key, $value);

    /**
     * Remove a key from the data
     *
     * @param string $key Key to remove
     */
    public function remove($key);

    /**
     * Get or set the content
     *
     * @param string|null $content Content to set
     * @return mixed
     */
    public function content($content = null);

    /**
     * Get or set the content type (extension)
     *
     * @param string|null $type
     * @return mixed
     */
    public function dataType($type = null);

    /**
     * Parse the content
     *
     * @return string
     */
    public function parseContent();

    /**
     * Get or set the path to the data
     *
     * @param string|null $path Path to set
     * @return mixed
     */
    public function path($path = null);

    /**
     * Get the supplemented data
     *
     * @return array
     */
    public function supplements();

    /**
     * Add supplemental data
     *
     * @return mixed
     */
    public function supplement();

    /**
     * Get a key in the supplemental data
     *
     * @param string     $key     Key to retrieve
     * @param mixed|null $default Fallback data
     * @return mixed
     */
    public function getSupplement($key, $default = null);

    /**
     * Set a key in the supplemental data
     *
     * @param string $key   Key to set
     * @param mixed  $value Value to set
     * @return mixed
     */
    public function setSupplement($key, $value);

    /**
     * Remove a key from the supplemental data
     *
     * @param string $key Key to remove
     * @return mixed
     */
    public function removeSupplement($key);

    /**
     * Get or set the fieldset
     *
     * @param string|null $fieldset
     * @return \Statamic\CP\Fieldset
     */
    public function fieldset($fieldset = null);

    /**
     * Save the data
     *
     * @return mixed
     */
    public function save();

    /**
     * Delete the data
     *
     * @return mixed
     */
    public function delete();
}
