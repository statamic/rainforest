<?php

namespace Statamic\Addons\Pages;

use Statamic\API\URL;
use Statamic\API\Str;
use Statamic\API\Content;
use Statamic\Addons\Collection\CollectionTags;

class PagesTags extends CollectionTags
{
    /**
     * Maps to `{{ pages }}`
     *
     * @return string
     */
    public function index()
    {
        $from = $this->get(['from', 'folder', 'url'], URL::getCurrent());

        $depth = $this->getInt('depth', 1);

        $from = Str::ensureLeft($from, '/');

        $this->collection = Content::pageRaw($from)->children($depth);

        $this->filter();

        if ($this->collection->isEmpty()) {
            return $this->parse(['no_results' => true]);
        }

        return $this->output();
    }

    /**
     * Alias of `{{ pages }}`
     *
     * @param string $method
     * @param array  $args
     * @return string
     **/
    public function listing()
    {
        return $this->index();
    }

    /**
     * Maps to `{{ pages:next }}`
     *
     * @return string
     */
    public function next()
    {
        $this->collectSequence();

        return $this->sequence('next');
    }

    /**
     * Maps to `{{ pages:previous }}`
     *
     * @return string
     */
    public function previous()
    {
        $this->collectSequence();

        return $this->sequence('previous');
    }

    /**
     * Set the collection for a sequence
     */
    private function collectSequence()
    {
        $from = $this->get(['from', 'folder', 'url'], URL::parent(URL::getCurrent()));

        $from = Str::ensureLeft($from, '/');

        $this->collection = Content::pageRaw($from)->children(1);
    }

    /**
     * Get the sort order of the collection
     *
     * @return string
     */
    protected function getSortOrder()
    {
        return $this->get('sort', 'order|title');
    }
}
