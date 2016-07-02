<?php

namespace Statamic\Contracts\Data\Entries;

use Statamic\Contracts\Data\Content\Content;

interface Entry extends Content
{
    /**
     * The collection to which this entry belongs
     *
     * @return \Statamic\Contracts\Data\Entries\CollectionFolder
     */
    public function collection();

    /**
     * The name of the collection to which this entry belongs
     *
     * @param string|null $collection
     * @return string
     */
    public function collectionName($collection = null);

    /**
     * Get the entry's date
     *
     * @return \Carbon\Carbon
     */
    public function date();

    /**
     * Does the entry have a timestamp?
     *
     * @return bool
     */
    public function hasTime();

    /**
     * Get the order type
     *
     * @return string
     */
    public function orderType();
}
