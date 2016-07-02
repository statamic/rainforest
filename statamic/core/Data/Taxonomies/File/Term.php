<?php

namespace Statamic\Data\Taxonomies\File;

use Statamic\API;
use Statamic\API\Str;
use Statamic\API\Fieldset;
use Statamic\API\URL;
use Statamic\API\Path;
use Statamic\API\YAML;
use Statamic\API\Config;
use Statamic\API\File;
use Statamic\Data\Content\File\Content;
use League\Flysystem\FileNotFoundException;
use Statamic\Data\Content\ContentCollection;
use Statamic\Contracts\Data\Taxonomies\Term as TermContract;

class Term extends Content implements TermContract
{
    /**
     * @var string
     */
    private $taxonomy;

    /**
     * @var string
     */
    private $slug;

    /**
     * @var \Statamic\Data\Content\ContentCollection
     */
    private $collection;

    /**
     * Create a new Taxonomy term
     *
     * @param string      $slug
     * @param array       $taxonomy
     * @param null|string $locale
     * @param array       $front_matter
     */
    public function __construct($slug, $taxonomy, $locale = null, $front_matter = [])
    {
        parent::__construct($locale, $front_matter);

        $this->slug = $slug;
        $this->taxonomy = $taxonomy;
    }

    /**
     * Get data from the folder.yaml
     *
     * @return array
     */
    protected function getFolderData()
    {
        return $this->taxonomy()->data();
    }

    /**
     * The group to which this taxonomy belongs
     *
     * @return \Statamic\Contracts\Data\Taxonomies\Taxonomy
     */
    public function taxonomy()
    {
        return \Statamic\API\Content::taxonomy($this->taxonomyName());
    }

    /**
     * @param string|null $taxonomy
     * @return string
     */
    public function taxonomyName($taxonomy = null)
    {
        if (is_null($taxonomy)) {
            return $this->taxonomy;
        }

        $this->taxonomy = $taxonomy;
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
            ->taxonomy()
            ->slug($this->slug())
            ->defaultSlug($this->slug)
            ->group($this->taxonomyName())
            ->published($this->published)
            ->order($this->order())
            ->locale($this->locale)
            ->get();
    }

    protected function setPath($path)
    {
        // TODO: Implement setPath() method.
    }

    /**
     * Get or set the URL
     *
     * @param string|null $url
     * @return mixed
     */
    public function url($url = null)
    {
        if (is_null($url)) {
            return $this->getUrl();
        }

        // Can't set URL directly.
    }

    /**
     * Get the URL of the taxonomy
     *
     * @return string
     */
    protected function getUrl()
    {
        $routes = array_get(Config::getRoutes(), 'taxonomies', []);

        if (! $route = array_get($routes, $this->taxonomyName())) {
            return false;
        }

        return app('Statamic\Contracts\Data\Content\UrlBuilder')->content($this)->build($route);
    }

    /**
     * Get the template for this taxonomy
     *
     * @return string
     */
    public function getTemplate()
    {
        return [
            $this->get('template'), // gets `template` from the entry, and falls back to what's in folder.yaml
            $this->taxonomyName(),
            Config::get('theming.default_taxonomy_template'),
            Config::get('theming.default_page_template')
        ];
    }

    protected function setTemplate($template)
    {
        // TODO: Implement setTemplate() method.
    }

    /**
     * Get the layout for this taxonomy
     *
     * @return string
     */
    public function getLayout()
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
     * Some data on the taxonomy is dynamic and only available through methods.
     * When we want to use these when preparing for use in a template for
     * example, we will need these available in the front-matter.
     */
    public function supplement()
    {
        parent::supplement();

        $this->supplements['taxonomy_group'] = $this->taxonomyName(); // @todo: remove
        $this->supplements['taxonomy'] = $this->taxonomyName();
        $this->supplements['count'] = $this->count();
        $this->supplements['is_term'] = true;

        $this->supplements = array_merge($this->getFolderData(), $this->supplements);
    }

    /**
     * Get or set content that is related to this taxonomy
     *
     * @param \Statamic\Data\Content\ContentCollection|null $collection
     * @return \Statamic\Data\Content\ContentCollection
     */
    public function collection($collection = null)
    {
        if (is_null($collection)) {
            return $this->getCollection();
        }

        $this->setCollection($collection);
    }

    /**
     * Get content that is related to this taxonomy
     *
     * @return \Statamic\Data\Content\ContentCollection
     */
    protected function getCollection()
    {
        if ($this->collection) {
            return $this->collection;
        }

        // If there's no ID, we're probably dealing with a temporary term, like from
        // within a Sneak Peek. In that case, don't bother. There are no entries.
        if (! $this->id()) {
            return collect_content();
        }

        return $this->collection = collect_content(
            API\Content::entries(null, null, true)->filterByTaxonomy($this->id())
        );
    }

    /**
     * Set the collection of content that is related to this taxonomy
     *
     * @param \Statamic\Data\Content\ContentCollection $collection
     */
    protected function setCollection(ContentCollection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * Get the number of content objects that related to this taxonomy
     *
     * @return int
     */
    public function count()
    {
        return $this->getCollection()->count();
    }

    /**
     * Convert this to an array (for use in templates)
     *
     * @return array
     */
    public function toArray()
    {
        $data = parent::toArray();

        return array_merge($data, [
            'collection' => $this->getCollection()->toArray(),
            'results' => $this->count()
        ]);
    }

    /**
     * Get this entry in the default locale
     *
     * @return \Statamic\Data\Entry
     */
    public function unlocalized()
    {
        if ($this->locale === Config::getDefaultLocale()) {
            return $this;
        }

        return \Statamic\API\TaxonomyTerm::getByUuid($this->data['id'], Config::getDefaultLocale());
    }

    /**
     * Get the taxonomy slug
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
     * Set the taxonomy slug
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
     * Get the URL of the taxonomy
     *
     * @return string
     */
    public function urlPath()
    {
        $routes = array_get(Config::getRoutes(), 'taxonomies', []);

        if (! $route = array_get($routes, $this->taxonomyName())) {
            return false;
        }

        return app('Statamic\Contracts\Data\Content\UrlBuilder')->content($this)->build($route);
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
     * Delete this taxonomy
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
     * Get the URL to edit in the CP
     *
     * @return string
     */
    public function editUrl()
    {
        return cp_route('term.edit', [$this->taxonomyName(), $this->slug()]);
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
}
