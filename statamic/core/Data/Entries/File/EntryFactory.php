<?php

namespace Statamic\Data\Entries\File;

use Statamic\API\Config;
use Statamic\Data\Content\File\ContentFactory;
use Statamic\Contracts\Data\Entries\EntryFactory as EntryFactoryContract;

class EntryFactory extends ContentFactory implements EntryFactoryContract
{
    protected $slug;
    protected $collection;

    /**
     * @param string $slug
     * @return $this
     */
    public function create($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @param string $collection
     * @return $this
     */
    public function collection($collection)
    {
        $this->collection = $collection;

        return $this;
    }

    /**
     * @return Entry
     */
    public function get()
    {
        $entry = new Entry($this->slug, $this->collection, $this->locale, $this->data);

        $entry->order($this->order);
        $entry->published($this->published);

        if ($this->path) {
            $entry->dataType(pathinfo($this->path)['extension']);
        } else {
            $entry->dataType(Config::get('system.default_extension'));
        }

        $entry->originalPath($this->path ?: $entry->path());

        return $entry;
    }
}
