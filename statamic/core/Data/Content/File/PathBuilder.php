<?php

namespace Statamic\Data\Content\File;

use Statamic\API\URL;
use Statamic\API\Page;
use Statamic\API\Path;
use Statamic\API\Config;
use Statamic\API\Str;
use Statamic\Contracts\Data\Content\PathBuilder as PathBuilderContract;

class PathBuilder implements PathBuilderContract
{
    protected $type = 'page';
    protected $url;
    protected $slug;
    protected $default_slug;
    protected $parent_path;
    protected $collection;
    protected $published = true;
    protected $order;
    protected $extension = 'md';
    protected $locale;

    public function __construct()
    {
        $this->locale = site_locale();
    }

    public function page()
    {
        $this->type = 'page';

        return $this;
    }

    public function entry()
    {
        $this->type = 'entry';

        return $this;
    }

    public function taxonomy()
    {
        $this->type = 'taxonomy';

        return $this;
    }

    public function url($url)
    {
        $this->url = Str::ensureLeft($url, '/');

        return $this;
    }

    public function slug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    public function defaultSlug($slug)
    {
        $this->default_slug = $slug;

        return $this;
    }

    public function collection($collection)
    {
        $this->collection = $collection;

        return $this;
    }

    public function group($group)
    {
        return $this->collection($group);
    }

    public function published($published)
    {
        $this->published = $published;

        return $this;
    }

    public function order($order)
    {
        $this->order = $order;

        return $this;
    }

    public function locale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    public function extension($extension)
    {
        $this->extension = $extension;

        return $this;
    }

    public function parentPath($path)
    {
        $this->parent_path = $path;

        return $this;
    }

    public function get()
    {
        if (in_array($this->type, ['entry', 'taxonomy']) && (! $this->collection || ! $this->slug)) {
            throw new \Exception('Collection and/or slug have not been specified.');
        }

        return $this->getFilename();
    }

    private function getFilename()
    {
        if ($this->type == 'page') {
            // pages/_1.slug/index.md
            // pages/_1.slug/fr.index.md
            return URL::tidy(
                $this->getParentPath() . $this->getStatusPrefix() . $this->getOrderPrefix() .
                $this->getSlug() . '/' . $this->getLocalePrefix() . 'index.' . $this->extension
            );
        }

        $path = ($this->type == 'entry') ? 'collections/'.$this->collection : 'taxonomies/'.$this->collection;

        // (collections|taxonomies)/blog/_1.slug.md
        // (collections|taxonomies)/blog/fr/_1.slug.md
        return Path::makeRelative($path) . '/' . $this->getLocalePrefix() .
               $this->getStatusPrefix() . $this->getOrderPrefix() . $this->getSlug() . '.' . $this->extension;
    }

    private function getStatusPrefix()
    {
        return ($this->published) ? '' : '_';
    }

    private function getOrderPrefix()
    {
        if ($this->order) {
            return $this->order . '.';
        }

        return '';
    }

    private function getLocalePrefix()
    {
        if (! $this->locale || $this->locale == Config::getDefaultLocale()) {
            return '';
        }

        $separator = ($this->type == 'page') ? '.' : '/';

        return $this->locale . $separator;
    }

    private function getSlug()
    {
        if ($this->type == 'page') {
            return URL::slug($this->url);
        }

        return ($this->locale == Config::getDefaultLocale())
            ? $this->slug
            : $this->default_slug;
    }

    private function getParentPath()
    {
        if ($this->parent_path) {
            $path = Str::ensureRight($this->parent_path, '/');

            $path = preg_replace('/^pages/', '', $path);

            return 'pages/'.$path;
        }

        $path = null;

        if ($this->url == '/') {
            $path = '';
        }

        if (substr_count($this->url, '/') > 1) {
            $parent = URL::parent($this->url);

            if (! $page = Page::getByUrl($parent)) {
                throw new \Exception("Parent page [$parent] doesn't exist.");
            }

            $path = $page->path();

            $path = Path::popLastSegment($path) . '/';
        }

        return $path ?: 'pages/'.$path;
    }
}
