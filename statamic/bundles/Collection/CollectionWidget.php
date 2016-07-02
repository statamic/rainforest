<?php

namespace Statamic\Addons\Collection;

use Statamic\API\Content;
use Statamic\Extend\Widget;

class CollectionWidget extends Widget
{
    public function html()
    {
        $collection = $this->get('collection');

        if (! Content::collectionExists($collection)) {
            return "Error: Collection [$collection] doesn't exist.";
        }

        $collection = Content::collection($collection);

        $entries = $collection->entries()
            ->removeUnpublished()
            ->limit($this->getInt('limit', 5));

        $title = $this->get('title', 'Recent Entries in ' . $collection->title());

        return $this->view('widget', compact('entries', 'title'));
    }
}
