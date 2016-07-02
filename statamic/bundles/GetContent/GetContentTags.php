<?php

namespace Statamic\Addons\GetContent;

use Statamic\API\Helper;
use Statamic\API\Content;
use Statamic\Addons\Collection\CollectionTags;

class GetContentTags extends CollectionTags
{
    /**
     * The {{ get_content }} tag
     *
     * @return string
     */
    public function index()
    {
        $url = Helper::explodeOptions($this->get('from'));

        $this->collection = Content::getRaw($url);

        $this->filter();

        return $this->output();
    }

    protected function getSortOrder()
    {
        return $this->get('sort');
    }
}
