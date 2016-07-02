<?php

namespace Statamic\Data\Pages\File;

use Statamic\API\Fieldset;
use Statamic\API\URL;
use Statamic\API\Path;
use Statamic\API\YAML;
use Statamic\API\Config;
use Statamic\API\Helper;
use Statamic\API\Str;
use Statamic\API\File;
use Statamic\API\Folder;
use Statamic\Data\Content\File\Content;
use Statamic\API\Content as ContentAPI;
use League\Flysystem\FileNotFoundException;
use Statamic\Contracts\Data\Pages\Page as PageContract;

/**
 * A page content data type
 */
class Page extends Content implements PageContract
{
    /**
     * @var string
     */
    private $url;

    /**
     * For overriding the parent path
     *
     * @var string
     */
    private $parent_path;

    /**
     * Create a new Page
     *
     * @param string $url
     * @param null|string $locale
     * @param array  $front_matter
     */
    public function __construct($url, $locale = null, $front_matter = [])
    {
        parent::__construct($locale, $front_matter);

        $this->url(Str::ensureLeft($url, '/'));
    }

    /**
     * Get data from folder.yaml files
     *
     * @return array
     */
    protected function getFolderData()
    {
        $path = Path::directory(Path::clean(preg_replace('/site\/content\/pages\//', '', $this->path())));

        $segments = explode('/', $path);

        $data = [];

        while (count($segments)) {
            $path = join('/', $segments);

            if ($folder = ContentAPI::pageFolder($path)) {
                $data = array_merge($folder->data(), $data);
            }

            array_pop($segments);
        }

        return $data;
    }

    /**
     * Get the URL path of the page
     *
     * @return string
     */
    public function urlPath()
    {
        $url = URL::buildFromPath($this->getPath());

        if ($this->locale !== Config::getDefaultLocale()) {
            $url = array_get(content_cache()->getLocalizedUrls(), $this->id(), $url);
        }

        return $url;
    }

    /**
     * Get the edit URL of the page
     *
     * @return string
     */
    public function editUrl()
    {
        return cp_route('page.edit', $this->slug());
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
            return parent::url();
        }

        $this->url = $url;
    }

    protected function getSlug()
    {
        if ($slug = array_get($this->data, 'slug')) {
            return $slug;
        }

        return URL::slug($this->url);
    }

    /**
     * Set the page slug
     *
     * @param $slug
     */
    protected function setSlug($slug)
    {
        if ($this->locale === Config::getDefaultLocale()) {
            // If this content belongs to the default locale, we want
            // to update the slug in the url property. It is not
            // stored in the front matter.
            $url = URL::replaceSlug($this->url, $slug);
            $this->url = $url;
        } else {
            // If this is *not* the default locale, we want to store the slug
            // in the front-matter and leave the property as-is. Also, we
            // only need to store the slug if it's different from the
            // default locale slug.
            if ($slug !== $this->getSlug()) {
                $this->data['slug'] = $slug;
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

        $builder = $this->pathBuilder()
            ->page()
            ->url($this->url)
            ->extension($this->extension ?: Config::get('system.default_extension'))
            ->published($this->published)
            ->order($this->order())
            ->locale($this->locale);

        if ($this->parent_path) {
            $builder->parentPath($this->parent_path);
        }

        return $builder->get();
    }

    /**
     * Set the path to the file
     *
     * @param string $path
     */
    protected function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Get or set the parent's path.
     *
     * Setting this will bypass some of the dynamic path building, so it should only be used
     * when absolutely necessary. One example is when saving pages in bulk.
     *
     * @param string $path  The parent's path. eg. 1.about/2.team/
     */
    public function parentPath($path = null)
    {
        if (is_null($path)) {
            return $this->getParentPath();
        }

        $this->parent_path = $path;
    }

    /**
     * Get the templates for this page
     *
     * @return array
     */
    protected function getTemplate()
    {
        return [
            $this->get('template'), // gets `template` from the entry, and falls back to what's in folder.yaml
            Config::get('theming.default_page_template')
        ];
    }

    protected function setTemplate($template)
    {
        // Todo
    }

    /**
     * Get the template for this page
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
        // Todo
    }

    /**
     * Set or get whether this page has entries
     *
     * @return bool|null
     */
    public function hasEntries()
    {
        return (bool) $this->get('mount');
    }

    /**
     * Get the entries mounted to this page
     *
     * @return \Statamic\Data\EntryCollection
     */
    public function entries()
    {
        if (! $this->hasEntries()) {
            return collect_content();
        }

        return ContentAPI::entries()->from($this->entriesCollection());
    }

    /**
     * Get the entries folder mounted to this page
     *
     * @return string
     */
    public function entriesCollection()
    {
        return $this->get('mount');
    }

    /**
     * Get this page's child pages
     *
     * @param null|int $depth
     * @return \Statamic\Data\Pages\PageCollection
     */
    public function children($depth = null)
    {
        $parent_url = $this->urlPath();

        // Get all the children
        $children = ContentAPI::pages()->filter(function($page) use ($parent_url) {
            return Str::startsWith($page->urlPath(), Str::ensureRight($parent_url, '/'));
        });

        // Remove pages that don't match the depth
        if ($depth) {
            $parent_slashes = substr_count($parent_url, '/');

            if ($parent_url === '/') {
                $parent_slashes = 0;
            }

            $children = $children->filter(function($page) use ($parent_url, $parent_slashes, $depth) {
                return $depth >= substr_count($page->urlPath(), '/') - $parent_slashes;
            });
        }

        // Remove the homepage, if we are the home page.
        if ($parent_url == '/') {
            $children = $children->forget($this->id());
        }

        return $children;
    }

    /**
     * Rename the file
     */
    protected function rename()
    {
        // The original file would have already been saved in its
        // new filename, so let's delete the old one.
        try {
            File::disk('content')->delete($this->original_path);
        } catch (FileNotFoundException $e) {
            // Nevermind, it's already gone.
        }

        // Move any localized and child pages
        $dir = Path::directory($this->original_path);
        $new_dir = Path::directory($this->path());
        Folder::disk('content')->rename($dir, $new_dir);

        // Delete any leftovers
        if (Folder::disk('content')->isEmpty($dir = Path::directory($this->original_path))) {
            Folder::disk('content')->delete($dir);
        }
    }

    /**
     * Delete this page
     */
    public function delete()
    {
        $folder = Path::directory($this->getPath());

        Folder::disk('content')->delete($folder);
    }

    /**
     * Get this page in the default locale
     *
     * @return \Statamic\Data\Page
     */
    public function unlocalized()
    {
        if ($this->locale === Config::getDefaultLocale()) {
            return $this;
        }

        return \Statamic\API\Page::getByUuid($this->data['id'], Config::getDefaultLocale());
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
     * Add supplemental data to the attributes
     *
     * Some data on the page is dynamic and only available through methods.
     * When we want to use these when preparing for use in a template for
     * example, we will need these available in the front-matter.
     */
    public function supplement()
    {
        parent::supplement();

        $this->supplements['is_page'] = true;

        $this->supplements = array_merge($this->getFolderData(), $this->supplements);
    }
}
