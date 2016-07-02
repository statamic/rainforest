<?php

namespace Statamic\Addons\Relate;

use Statamic\API\Str;
use Statamic\API\User;
use Statamic\API\Helper;
use Statamic\API\Content;
use Statamic\API\Pattern;
use Statamic\Data\ContentCollection;
use Statamic\Addons\Collection\CollectionTags;

class RelateTags extends CollectionTags
{
    public function __call($method, $args)
    {
        $var = Str::snake($method);

        $this->collection = collect_content();

        $values = Helper::ensureArray(array_get($this->context, $var, []));

        foreach ($values as $value) {
            $content = (Pattern::isUUID($value)) ? $this->getRelation($value) : Content::getRaw($value);

            if (! $content) {
                continue;
            }

            $this->collection->push($content);
        }

        $this->filter();

        if ($this->collection->isEmpty()) {
            return $this->parse(['no_results' => true]);
        }

        return $this->output();
    }

    private function getRelation($id)
    {
        // First try content
        if ($content = Content::uuidRaw($id)) {
            return $content;
        }

        // Then users
        if ($user = User::get($id)) {
            return $user;
        }
    }

    protected function getSortOrder()
    {
        return $this->get('sort');
    }
}
