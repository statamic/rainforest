<?php

namespace Statamic\Data\Entries\File;

use Carbon\Carbon;
use Statamic\API\Fieldset;
use Statamic\API\Path;
use Statamic\API\YAML;
use Statamic\API\Config;
use Statamic\API\File;
use Statamic\Data\Content\File\Content;
use League\Flysystem\FileNotFoundException;
use Statamic\Exceptions\InvalidEntryTypeException;
use Statamic\Contracts\Data\Entries\Entry as EntryContract;

/**
 * An entry content data type
 */
class Entry extends Content implements EntryContract
{
    /**
     * @var string
     */
    private $slug;

    /**
     * @var string
     */
    private $collection;

    /**
     * Create a new Entry
     *
     * @param string      $slug
     * @param array       $collection
     * @param null|string $locale
     * @param array       $front_matter
     */
    public function __construct($slug, $collection, $locale = null, $front_matter = [])
    {
        parent::__construct($locale, $front_matter);

        $this->slug = $slug;
        $this->collection = $collection;
    }

    /**
     * Get data from the folder.yaml
     *
     * @return array
     */
    protected function getFolderData()
    {
        return $this->collection()->data();
    }

    /**
     * Get the entry slug
     *
     * @return string
     */
    public function getSlug()
    {
        if ($slug = array_get($this->data, 'slug')) {
            return $slug;
        }

        $slug = $this->slug;

        // Remove any hidden/draft indicators
        return ltrim($slug, '__');
    }

    /**
     * Set the entry slug
     *
     * @param $slug
     */
    public function setSlug($slug)
    {
        if ($this->locale === Config::getDefaultLocale()) {
            // If this content belongs to the default locale, we want to update
            // the slug property. It is not stored in the front matter.
            $this->slug = $slug;
        } else {
            // If this is *not* the default locale, we want to store the slug
            // in the front-matter and leave the property as-is. Also, we
            // only need to store the slug if it's different from the
            // default locale slug.
            if ($slug !== $this->getSlug()) {
                $this->set('slug', $slug);
            }
        }
    }

    /**
     * The collection to which this entry belongs
     *
     * @return \Statamic\Contracts\Data\Entries\CollectionFolder
     */
    public function collection()
    {
        return \Statamic\API\Content::collection($this->collectionName());
    }

    /**
     * The name of the collection to which this entry belongs
     *
     * @param string|null $collection
     * @return string
     */
    public function collectionName($collection = null)
    {
        return $this->collection;
    }

    /**
     * Get the URL of the entry
     *
     * @return string
     */
    public function urlPath()
    {
        $routes = array_get(Config::getRoutes(), 'collections', []);

        if (! $route = array_get($routes, $this->collectionName())) {
            return false;
        }

        return app('Statamic\Contracts\Data\Content\UrlBuilder')->content($this)->build($route);
    }

    /**
     * Get the edit URL of the entry
     *
     * @return string
     */
    public function editUrl()
    {
        return cp_route('entry.edit', $this->collectionName() . '/' . $this->slug());
    }

    /**
     * Delete this entry
     */
    public function delete()
    {
        File::disk('content')->delete($this->getPath());

        // Delete other locales if deleting the default one
        if (! $this->isLocalized()) {
            foreach (Config::getOtherLocales() as $locale) {
                $parts = explode('/', $this->path());
                array_splice($parts, 2, 0, [$locale]);
                $path = join('/', $parts);

                try {
                    File::disk('content')->delete($path);
                } catch (FileNotFoundException $e) {
                    // File doesn't exist, move on.
                }
            }
        }
    }

    /**
     * Get the path to the file
     *
     * @return string
     */
    protected function getPath()
    {
        if ($this->path) {
            return $this->path;
        }

        return $this->pathBuilder()
            ->entry()
            ->slug($this->slug())
            ->defaultSlug($this->slug)
            ->collection($this->collectionName())
            ->published($this->published)
            ->order($this->order())
            ->extension($this->dataType())
            ->locale($this->locale)
            ->get();
    }

    protected function setPath($path)
    {
        // Todo
    }

    /**
     * Get the entry's date
     *
     * @return \Carbon\Carbon
     * @throws \Statamic\Exceptions\InvalidEntryTypeException
     */
    public function date()
    {
        if ($this->orderType() !== 'date') {
            throw new InvalidEntryTypeException(
                sprintf('Cannot get the date on an non-date based entry: [%s]', $this->path())
            );
        }

        if (substr_count($this->order(), '-') < 1) {
            throw new InvalidEntryTypeException(
                sprintf('Entry date not present in a date-based entry: [%s]', $this->path())
            );
        }

        return (strlen($this->order()) == 15)
               ? Carbon::createFromFormat('Y-m-d-Hi', $this->order())
               : Carbon::createFromFormat('Y-m-d', $this->order())->startOfDay();
    }

    /**
     * Does the entry have a timestamp?
     *
     * @return bool
     */
    public function hasTime()
    {
        return $this->orderType() === 'date' && strlen($this->order()) === 15;
    }

    /**
     * Get the order type
     *
     * @return string
     */
    public function orderType()
    {
        return $this->collection()->order();
    }

    /**
     * Get the fieldset for the chosen entry
     *
     * @return \Statamic\CP\Fieldset
     */
    protected function getFieldset()
    {
        // First check the front matter
        if ($fieldset = $this->get('fieldset')) {
            return Fieldset::get($fieldset);
        }

        // Then the default content fieldset
        $fieldset = Config::get('theming.default_' . $this->contentType() . '_fieldset');
        $path = settings_path('fieldsets/'.$fieldset.'.yaml');
        if (File::exists($path)) {
            return Fieldset::get($fieldset);
        }

        // Finally the default fieldset
        return Fieldset::get(Config::get('theming.default_fieldset'));
    }

    /**
     * Get the templates for this entry
     *
     * @return array
     */
    protected function getTemplate()
    {
        return [
            $this->get('template'), // gets `template` from the entry, and falls back to what's in folder.yaml
            Config::get('theming.default_entry_template'),
            Config::get('theming.default_page_template')
        ];
    }

    protected function setTemplate($template)
    {
        // TODO: Implement setTemplate() method.
    }

    /**
     * Get the layout for this entry
     *
     * @return string
     */
    protected function getLayout()
    {
        // First, check the front-matter
        if ($layout = $this->get('layout')) {
            return $layout;
        }

        // Lastly, return a default
        return Config::get('theming.default_layout');
    }

    protected function setLayout($layout)
    {
        // TODO: Implement setLayout() method.
    }

    /**
     * Add supplemental data to the attributes
     *
     * Some data on the entry is dynamic and only available through methods.
     * When we want to use these when preparing for use in a template for
     * example, we will need these available in the front-matter.
     */
    public function supplement()
    {
        parent::supplement();

        if ($this->orderType() === 'date') {
            $this->supplements['date'] = $this->date();
            $this->supplements['datestring'] = $this->date()->__toString();
            $this->supplements['datestamp'] = $this->date()->timestamp;
            $this->supplements['timestamp'] = $this->date()->timestamp;
            $this->supplements['has_timestamp'] = $this->hasTime();
        }

        $this->supplements['order_type'] = $this->orderType();
        $this->supplements['collection'] = $this->collectionName();
        $this->supplements['is_entry'] = true;

        $this->supplements = array_merge($this->getFolderData(), $this->supplements);
    }

    /**
     * Rename the file
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
     * Get the unlocalized version of this content
     *
     * @return \Statamic\Contracts\Data\Content\Content
     */
    public function unlocalized()
    {
        if ($this->locale === Config::getDefaultLocale()) {
            return $this;
        }

        return \Statamic\API\Entry::getByUuid($this->data['id'], Config::getDefaultLocale());
    }
}
