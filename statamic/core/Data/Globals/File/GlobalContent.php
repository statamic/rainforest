<?php

namespace Statamic\Data\Globals\File;

use Statamic\API\Fieldset;
use Statamic\API\YAML;
use Statamic\API\Path;
use Statamic\API\Config;
use Statamic\API\Globals;
use Statamic\API\File;
use Statamic\Data\Content\File\Content;
use League\Flysystem\FileNotFoundException;
use Statamic\Contracts\Data\Globals\GlobalContent as GlobalContentContract;

class GlobalContent extends Content implements GlobalContentContract
{
    /**
     * @var string
     */
    private $slug;

    /**
     * @param null|string $slug
     * @param null|string $locale
     * @param array       $front_matter
     */
    public function __construct($slug, $locale = null, $front_matter = [])
    {
        parent::__construct($locale, $front_matter);

        $this->extension = 'yaml';
        $this->slug = $slug;
    }

    /**
     * Get the unlocalized version of this object
     *
     * @return \Statamic\Contracts\Data\Globals\GlobalContent
     */
    public function unlocalized()
    {
        if ($this->locale === default_locale()) {
            return $this;
        }

        return Globals::getByUuid($this->data['id'], default_locale());
    }

    /**
     * Get the slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    protected function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * Get the URL path
     *
     * @return string
     */
    public function urlPath()
    {
        return null;
    }

    /**
     * Get the template
     *
     * @return mixed
     */
    public function getTemplate()
    {
        return null;
    }

    protected function setTemplate($template)
    {
    }

    /**
     * Get the layout
     *
     * @return mixed
     */
    public function getLayout()
    {
        return null;
    }

    protected function setLayout($layout)
    {
    }

    /**
     * Rename the file
     *
     * @return mixed
     */
    public function rename()
    {
        // The original file would have already been saved in its
        // new filename, so let's delete the old one.
        File::disk('content')->delete($this->original_path);

        // Move localized versions
        foreach (Config::getOtherLocales() as $locale) {
            $old_path_basename = pathinfo($this->original_path)['basename'];
            $old_path_dir      = Path::directory($this->original_path);
            $old_path          = $old_path_dir . '/' . $locale . '/' . $old_path_basename;

            $new_path_basename = pathinfo($this->path())['basename'];
            $new_path_dir      = Path::directory($this->path());
            $new_path          = $new_path_dir . '/' . $locale . '/' . $new_path_basename;

            try {
                File::disk('content')->rename($old_path, $new_path);
            } catch (FileNotFoundException $e) {
                // Entry doesn't exist for this locale.
            }
        }
    }

    /**
     * Get the path to the file
     *
     * @return string
     */
    public function getPath()
    {
        if ($this->path) {
            return $this->path;
        }

        $locale_prefix = ($this->locale !== default_locale()) ? $this->locale . '/' : '';

        return Path::makeRelative('globals/' . $locale_prefix . $this->slug . '.yaml');
    }

    protected function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Delete the data
     *
     * @return mixed
     */
    public function delete()
    {
        File::disk('content')->delete($this->getPath());

        // Delete other locales if deleting the default one
        if (! $this->isLocalized()) {
            foreach (Config::getOtherLocales() as $locale) {
                $path = Path::replaceSlug($this->path(), $locale . '/' . $this->slug() . '.yaml');

                try {
                    File::disk('content')->delete($path);
                } catch (FileNotFoundException $e) {
                    // File doesn't exist, move on.
                }
            }
        }
    }

    /**
     * Get the fieldset
     *
     * @return \Statamic\CP\Fieldset
     */
    public function getFieldset()
    {
        $fieldset = $this->get('fieldset', 'globals');

        $fieldset = Fieldset::get($fieldset);

        $fieldset->type('global');

        return $fieldset;
    }

    public function setFieldset($fieldset)
    {
        $this->set('fieldset', $fieldset);
    }

    public function title($title = null)
    {
        if (! is_null($title)) {
            $this->set('title', $title);
        }

        if ($title === false) {
            $this->set('title', null);
        }

        $fallback = ($this->getSlug() === 'global')
                    ? translate('cp.general_globals')
                    : ucfirst($this->getSlug());

        return $this->get('title', $fallback);
    }

    /**
     * Convert to an array
     *
     * @return array
     */
    public function toArray()
    {
        $arr = parent::toArray();

        $unsets = ['content', 'content_raw', 'permalink', 'status', 'order', 'url', 'url_path'];

        foreach ($unsets as $unset) {
            unset($arr[$unset]);
        }

        return $arr;
    }

    /**
     * Get the URL to edit in the CP
     *
     * @return string
     */
    public function editUrl()
    {
        return cp_route('globals.edit', $this->slug());
    }

    /**
     * Get data from the folder.yaml
     *
     * @return array
     */
    protected function getFolderData()
    {
        return [];
    }
}
